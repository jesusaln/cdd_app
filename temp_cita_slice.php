                'precio' => (float) ($producto->pivot->precio_venta ?? $producto->precio_venta ?? 0),
            ];
        }
        foreach ($cita->serviciosRealizados as $servicio) {
            $items[] = [
                'modelo' => $servicio,
                'tipo' => 'servicio',
                'cantidad' => (int) ($servicio->pivot->cantidad ?? 1),
                'precio' => (float) ($servicio->pivot->precio ?? $servicio->precio ?? 0),
            ];
        }

        if (empty($items)) {
            return; // No hay nada que vender, salir silenciosamente
        }

        // Validar stock disponible para productos
        foreach ($items as $it) {
            if ($it['tipo'] === 'producto') {
                $producto = $it['modelo'];
                $cantidad = $it['cantidad'];
                if ($producto->stock_disponible < $cantidad) {
                    throw ValidationException::withMessages([
                        'productos' => "Stock insuficiente para '{$producto->nombre}'. Disponible: {$producto->stock_disponible}, Solicitado: {$cantidad}",
                    ]);
                }
            }
        }

        // Crear venta
        $venta = Venta::create([
            'cliente_id' => $cita->cliente_id,
            'numero_venta' => $this->generarNumeroVenta(),
            'fecha' => now()->toDateString(),
            'estado' => EstadoVenta::Pendiente,
            'subtotal' => 0,
            'descuento_general' => 0,
            'iva' => 0,
            'total' => 0,
            'vendedor_type' => $cita->tecnico ? get_class($cita->tecnico) : null,
            'vendedor_id' => $cita->tecnico_id,
        ]);

        $subtotal = 0;
        foreach ($items as $it) {
            $modelo = $it['modelo'];
            $cantidad = $it['cantidad'];
            $precio = $it['precio'];
            $descuento = 0;
            $lineaSubtotal = $cantidad * $precio;
            $subtotal += $lineaSubtotal;

            VentaItem::create([
                'venta_id' => $venta->id,
                'ventable_id' => $modelo->id,
                'ventable_type' => get_class($modelo),
                'cantidad' => $cantidad,
                'precio' => $precio,
                'descuento' => $descuento,
                'subtotal' => $lineaSubtotal,
            ]);
        }

        // Actualizar totales simples (sin IVA configurable aquí)
        $venta->update([
            'subtotal' => $subtotal,
            'iva' => 0,
            'total' => $subtotal,
        ]);

        // Descontar inventario para productos
        $inventarioService = app(InventarioService::class);
        foreach ($items as $it) {
            if ($it['tipo'] === 'producto') {
                $producto = $it['modelo'];
                $cantidad = $it['cantidad'];
                $inventarioService->salida($producto, $cantidad, [
                    'motivo' => 'Venta generada desde cita completada',
                    'referencia' => $venta,
                    'detalles' => [
                        'cita_id' => $cita->id,
                    ],
                    'user_id' => $user?->id,
                ]);
            }
        }

        // Marcar en los pivotes de productos vendidos el id de venta
        foreach ($cita->productosVendidos as $producto) {
            $cita->productosVendidos()->updateExistingPivot($producto->id, [
                'venta_id' => $venta->id,
            ]);
        }
    }

    /**
     * Genera un número de venta único.
     */
    private function generarNumeroVenta(): string
    {
        $ultimo = Venta::orderBy('id', 'desc')->first();
        $numero = $ultimo ? $ultimo->id + 1 : 1;
        return 'VEN-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Método mejorado para guardar archivos
     */
    private function saveFiles(Request $request, array $fileFields, $existingFiles = [])
    {
        $filePaths = [];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                try {
                    $file = $request->file($field);

                    // Generar nombre único para evitar conflictos
                    $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $extension = $file->getClientOriginalExtension();
                    $filename = $originalName . '_' . now()->format('YmdHis') . '_' . substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyz'), 0, 6) . '.' . $extension;

                    $path = $file->storeAs('citas', $filename, 'public');
                    $filePaths[$field] = $path;

                    // Eliminar el archivo anterior si existe
                    if (!empty($existingFiles[$field])) {
                        Storage::disk('public')->delete($existingFiles[$field]);
                    }
                } catch (\Exception $e) {
                    Log::error("Error al guardar el archivo {$field}: " . $e->getMessage());
                    $filePaths[$field] = $existingFiles[$field] ?? null;
                }
            } else {
                $filePaths[$field] = $existingFiles[$field] ?? null; // Conservar el archivo existente
            }
        }
        return $filePaths;
    }

    /**
     * Verificar disponibilidad del técnico
     */
    private function verificarDisponibilidadTecnico(int $tecnicoId, string $fechaHora, ?int $excludeId = null): void
    {
        $query = Cita::where('tecnico_id', $tecnicoId)
            ->where('fecha_hora', $fechaHora)
            ->where('estado', '!=', 'cancelado');

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'fecha_hora' => 'El técnico ya tiene una cita programada en esta fecha y hora.'
            ]);
        }
    }

    /**
      * Eliminar una cita existente.
      */
    public function destroy(Cita $cita)
    {
        try {
            DB::beginTransaction();

            // Verificar si se puede eliminar la cita
            $this->verificarPuedeEliminar($cita);

            // Eliminar archivos asociados
            $archivos = [
                $cita->foto_equipo,
                $cita->foto_hoja_servicio,
                $cita->foto_identificacion
            ];

            foreach ($archivos as $archivo) {
                if ($archivo && Storage::disk('public')->exists($archivo)) {
                    Storage::disk('public')->delete($archivo);
                }
            }

            $cita->delete();

            DB::commit();

            return redirect()->route('citas.index')->with('success', 'Cita eliminada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar cita: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'Error al eliminar la cita.');
        }
    }

    /**
      * Mostrar detalles de una cita.
      */
    public function show(Cita $cita)
    {
        $cita->load(['cliente', 'tecnico', 'productosUtilizados', 'productosVendidos', 'serviciosRealizados', 'items.citable']);

        return Inertia::render('Citas/Show', [
            'cita' => $cita,
        ]);
    }


    public function export(Request $request)
    {
        try {
            $query = Cita::with('tecnico', 'cliente');

            // Aplicar los mismos filtros que en index
            if ($s = trim((string) $request->input('search', ''))) {
                $query->where(function ($w) use ($s) {
                    $w->where('tipo_servicio', 'like', "%{$s}%")
                        ->orWhere('descripcion', 'like', "%{$s}%")
                        ->orWhereHas('cliente', function($clienteQuery) use ($s) {
                            $clienteQuery->where('nombre_razon_social', 'like', "%{$s}%");
                        })
                        ->orWhereHas('tecnico', function($tecnicoQuery) use ($s) {
                            $tecnicoQuery->where('nombre', 'like', "%{$s}%");
                        });
                });
            }

            if ($request->filled('estado')) {
                $query->where('estado', $request->estado);
            }

            if ($request->filled('tecnico_id')) {
                $query->where('tecnico_id', $request->tecnico_id);
            }

            if ($request->filled('cliente_id')) {
                $query->where('cliente_id', $request->cliente_id);
            }

            if ($request->filled('fecha_desde')) {
                $query->whereDate('fecha_hora', '>=', $request->fecha_desde);
            }

            if ($request->filled('fecha_hasta')) {
                $query->whereDate('fecha_hora', '<=', $request->fecha_hasta);
            }


            $citas = $query->get();

            $filename = 'citas_' . date('Y-m-d_H-i-s') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function () use ($citas) {
                $file = fopen('php://output', 'w');

                fputcsv($file, [
                    'ID',
                    'Cliente',
                    'Técnico',
                    'Tipo Servicio',
                    'Fecha y Hora',
                    'Estado',
                    'Prioridad',
                    'Tipo Equipo',
                    'Marca',
                    'Modelo',
                    'Fecha Creación'
                ]);

                foreach ($citas as $cita) {
                    fputcsv($file, [
                        $cita->id,
                        $cita->cliente?->nombre_razon_social ?? 'N/A',
                        $cita->tecnico?->nombre ?? 'N/A',
                        $cita->tipo_servicio,
                        $cita->fecha_hora?->format('d/m/Y H:i:s'),
                        $cita->estado,
                        $cita->prioridad ?? 'N/A',
                        'N/A',
                        'N/A',
                        'N/A',
                        $cita->created_at?->format('d/m/Y H:i:s')
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (Exception $e) {
            Log::error('Error en exportación de citas: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al exportar las citas.');
        }
    }

    /**
     * Verificar límite de citas por día para un técnico
     */
    private function verificarLimiteCitasPorDia(int $tecnicoId, string $fechaHora): void
    {
        $fecha = Carbon::parse($fechaHora)->toDateString();
        $inicioDia = Carbon::parse($fecha)->startOfDay();
        $finDia = Carbon::parse($fecha)->endOfDay();

        $citasEnDia = Cita::where('tecnico_id', $tecnicoId)
            ->whereBetween('fecha_hora', [$inicioDia, $finDia])
            ->where('estado', '!=', 'cancelado')
            ->count();

        // Límite de 8 citas por día
        if ($citasEnDia >= 8) {
            throw ValidationException::withMessages([
                'fecha_hora' => 'El técnico ya tiene el máximo de 8 citas programadas para este día.'
            ]);
        }
    }

    /**
     * Verificar que el cliente no tenga múltiples citas activas
     */
    private function verificarCitasClienteActivas(int $clienteId, string $fechaHora): void
    {
        $fecha = Carbon::parse($fechaHora);

        // Verificar si el cliente tiene más de 2 citas activas en los próximos 7 días
        $citasActivas = Cita::where('cliente_id', $clienteId)
            ->whereIn('estado', ['pendiente', 'en_proceso'])
            ->where('fecha_hora', '>=', now())
            ->where('fecha_hora', '<=', now()->addDays(7))
            ->count();

        if ($citasActivas >= 2) {
            throw ValidationException::withMessages([
                'cliente_id' => 'El cliente ya tiene múltiples citas activas. Complete las citas existentes antes de programar nuevas.'
            ]);
        }

        // Verificar si hay conflicto de horario el mismo día
        $citasMismoDia = Cita::where('cliente_id', $clienteId)
            ->whereDate('fecha_hora', $fecha->toDateString())
            ->where('estado', '!=', 'cancelado')
            ->where('fecha_hora', '!=', $fechaHora)
            ->count();

        if ($citasMismoDia > 0) {
            throw ValidationException::withMessages([
                'fecha_hora' => 'El cliente ya tiene una cita programada para este día.'
            ]);
        }
    }

    /**
      * Verificar si se puede eliminar la cita (sin relaciones críticas)
      */
    private function verificarPuedeEliminar(Cita $cita): void
    {
        // No permitir eliminar citas completadas con menos de 30 días de antigüedad
        if ($cita->estado === Cita::ESTADO_COMPLETADO) {
            $diasDesdeCreacion = now()->diffInDays($cita->created_at);
            if ($diasDesdeCreacion < 30) {
                throw ValidationException::withMessages([
                    'cita' => 'No se pueden eliminar citas completadas con menos de 30 días de antigüedad por políticas de auditoría.'
                ]);
            }
        }

        // Verificar si la cita está en proceso (solo permitir cancelación)
        if ($cita->estado === Cita::ESTADO_EN_PROCESO) {
            throw ValidationException::withMessages([
                'cita' => 'No se puede eliminar una cita en proceso. Solo se puede cancelar.'
            ]);
        }
    }

    /**
      * Convertir cita a pedido
      */
    public function convertirAPedido($id)
    {
        try {
            DB::beginTransaction();

            $cita = Cita::with(['cliente', 'items.citable'])->findOrFail($id);

            // Validar que la cita tenga items
            if ($cita->items->isEmpty()) {
                return redirect()->back()->with('error', 'La cita no tiene productos o servicios para convertir a pedido');
            }

            // Validar que la cita esté completada o en proceso
            if (!in_array($cita->estado, [Cita::ESTADO_COMPLETADO, Cita::ESTADO_EN_PROCESO])) {
                return redirect()->back()->with('error', 'Solo citas completadas o en proceso pueden convertirse a pedido');
            }

            // Crear pedido
            $pedido = Pedido::create([
                'cliente_id' => $cita->cliente_id,
                'cotizacion_id' => null, // No hay cotización asociada
                'numero_pedido' => $this->generarNumeroPedido(),
                'fecha' => now(),
                'estado' => EstadoPedido::Confirmado,
                'subtotal' => $cita->subtotal,
                'descuento_general' => $cita->descuento_general,
                'descuento_items' => $cita->descuento_items,
                'iva' => $cita->iva,
                'total' => $cita->total,
                'notas' => "Generado desde Cita #{$cita->id}",
            ]);

            // Copiar items
            foreach ($cita->items as $item) {
                PedidoItem::create([
                    'pedido_id' => $pedido->id,
                    'pedible_id' => $item->citable_id,
                    'pedible_type' => $item->citable_type,
                    'cantidad' => $item->cantidad,
                    'precio' => $item->precio,
                    'descuento' => $item->descuento,
                    'subtotal' => $item->subtotal,
                    'descuento_monto' => $item->descuento_monto,
                ]);
            }

            // Reservar inventario para productos
            foreach ($cita->items as $item) {
                if ($item->citable_type === Producto::class) {
                    $producto = Producto::find($item->citable_id);
                    if ($producto && $producto->stock_disponible >= $item->cantidad) {
                        $producto->increment('reservado', $item->cantidad);
                        Log::info("Inventario reservado para producto en pedido", [
                            'producto_id' => $producto->id,
                            'pedido_id' => $pedido->id,
                            'cita_id' => $cita->id,
                            'cantidad_reservada' => $item->cantidad,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('pedidos.show', $pedido->id)->with('success', 'Pedido creado exitosamente desde la cita');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al convertir cita a pedido', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Error al crear el pedido');
        }
    }

    /**
      * Convertir cita a venta
      */
    public function convertirAVenta($id)
    {
        try {
            DB::beginTransaction();

            $cita = Cita::with(['cliente', 'items.citable'])->findOrFail($id);

            // Validar que la cita tenga items
            if ($cita->items->isEmpty()) {
                return redirect()->back()->with('error', 'La cita no tiene productos o servicios para convertir a venta');
            }

            // Validar que la cita esté completada
            if ($cita->estado !== Cita::ESTADO_COMPLETADO) {
                return redirect()->back()->with('error', 'Solo citas completadas pueden convertirse a venta');
            }

            // Validar stock para productos
            foreach ($cita->items as $item) {
                if ($item->citable_type === Producto::class) {
                    $producto = Producto::find($item->citable_id);
                    if ($producto && $producto->stock_disponible < $item->cantidad) {
                        return redirect()->back()->with('error', "Stock insuficiente para '{$producto->nombre}'. Disponible: {$producto->stock_disponible}, Solicitado: {$item->cantidad}");
                    }
                }
            }

            // Crear venta
            $venta = Venta::create([
                'cliente_id' => $cita->cliente_id,
                'numero_venta' => $this->generarNumeroVenta(),
                'fecha' => now()->toDateString(),
                'estado' => EstadoVenta::Pendiente,
                'subtotal' => $cita->subtotal,
                'descuento_general' => $cita->descuento_general,
                'descuento_items' => $cita->descuento_items,
                'iva' => $cita->iva,
                'total' => $cita->total,
                'vendedor_type' => $cita->tecnico ? get_class($cita->tecnico) : null,
                'vendedor_id' => $cita->tecnico_id,
                'notas' => "Generado desde Cita #{$cita->id}",
            ]);

            // Crear items de venta
            foreach ($cita->items as $item) {
                VentaItem::create([
                    'venta_id' => $venta->id,
                    'ventable_id' => $item->citable_id,
                    'ventable_type' => $item->citable_type,
                    'cantidad' => $item->cantidad,
                    'precio' => $item->precio,
                    'descuento' => $item->descuento,
                    'subtotal' => $item->subtotal,
                ]);
            }

            // Descontar inventario para productos
            $inventarioService = app(InventarioService::class);
            foreach ($cita->items as $item) {
                if ($item->citable_type === Producto::class) {
                    $producto = Producto::find($item->citable_id);
                    $inventarioService->salida($producto, $item->cantidad, [
                        'motivo' => 'Venta generada desde cita completada',
                        'referencia' => $venta,
                        'detalles' => [
                            'cita_id' => $cita->id,
                        ],
                        'user_id' => auth()->id(),
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('ventas.show', $venta->id)->with('success', 'Venta creada exitosamente desde la cita');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al convertir cita a venta', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Error al crear la venta');
        }
    }

