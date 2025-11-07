
            $descuentoGeneralMonto = $request->descuento_general ?? 0;
            $subtotalDespuesGeneral = $subtotal - $descuentoGeneralMonto;

            $descuentoItems = 0;
            foreach ($validated['productos'] as $item) {
                $subtotalItem = $item['cantidad'] * $item['precio'];
                $descuentoItems += $subtotalItem * ($item['descuento'] / 100);
            }

            $subtotalFinal = $subtotalDespuesGeneral - $descuentoItems;
            $ivaRate = \App\Services\EmpresaConfiguracionService::getIvaPorcentaje() / 100;
            $iva = $subtotalFinal * $ivaRate;
            $total = $subtotalFinal + $iva;

            $numero_venta = $this->generarNumeroVenta();

            $venta = Venta::create([
                'cliente_id' => $validated['cliente_id'],
                'almacen_id' => $validated['almacen_id'],
                'vendedor_type' => $validated['vendedor_type'] ?? null,
                'vendedor_id' => $validated['vendedor_id'] ?? null,
                'factura_id' => null, // Puede llenarse si se asocia con una factura
                'numero_venta' => $numero_venta,
                'subtotal' => $subtotal,
                'descuento_general' => $descuentoGeneralMonto,
                'iva' => $iva,
                'total' => $total,
                'fecha' => now(),
                'estado' => EstadoVenta::Borrador,
                'notas' => $request->notas,
                'pagado' => false, // Asegurar que no esté pagado inicialmente
            ]);

            // Crear cuenta por cobrar si la venta no está pagada
            if (!$venta->pagado) {
                CuentasPorCobrar::create([
                    'venta_id' => $venta->id,
                    'monto_total' => $venta->total,
                    'monto_pagado' => 0,
                    'monto_pendiente' => $venta->total,
                    'fecha_vencimiento' => now()->addDays(30), // 30 días por defecto
                    'estado' => 'pendiente',
                    'notas' => 'Cuenta por cobrar generada automáticamente',
                ]);
            }

            // Crear items y reducir inventario
            foreach ($validated['productos'] as $item) {
                $class = $item['tipo'] === 'producto' ? Producto::class : Servicio::class;
                $modelo = $class::find($item['id']);

                if (!$modelo) {
                    Log::warning("ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Âtem no encontrado: {$class} con ID {$item['id']}");
                    continue;
                }

                $subtotalItem = $item['cantidad'] * $item['precio'];
                $descuentoMontoItem = $subtotalItem * ($item['descuento'] / 100);

                // Calcular costo histórico correcto para productos
                $costoUnitario = 0;
                if ($item['tipo'] === 'producto') {
                    // Usar costo histórico basado en lotes o movimientos recientes
                    $costoUnitario = $modelo->calcularCostoPorLotes($item['cantidad']);
                }

                $ventaItem = VentaItem::create([
                    'venta_id' => $venta->id,
                    'ventable_id' => $item['id'],
                    'ventable_type' => $class,
                    'cantidad' => $item['cantidad'],
                    'precio' => $item['precio'],
                    'descuento' => $item['descuento'],
                    'subtotal' => $subtotalItem,
                    'descuento_monto' => $descuentoMontoItem,
                    'costo_unitario' => $costoUnitario,
                ]);

                // Reducir inventario y marcar series para productos (priorizar reservas)
                if ($item['tipo'] === 'producto') {
                    // Si requiere serie, validar existencia y marcar como vendidas
                    if (($modelo->requiere_serie ?? false)) {
                        $seriales = $item['seriales'] ?? [];
                        $seriesEnStock = \App\Models\ProductoSerie::where('producto_id', $modelo->id)
                            ->whereIn('numero_serie', array_map('strval', $seriales))
                            ->where('estado', 'en_stock')
                            ->pluck('id');

                        if (count($seriales) !== $seriesEnStock->count()) {
                            throw new \Exception("Algunas series no existen o no están en stock para el producto '{$modelo->nombre}'.");
                        }

                        \App\Models\ProductoSerie::whereIn('id', $seriesEnStock->all())->update(['estado' => 'vendido']);
                    }

                    // Reducir stock específicamente del almacén seleccionado
                    $inventarioAlmacen = \App\Models\Inventario::where('producto_id', $modelo->id)
                        ->where('almacen_id', $validated['almacen_id'])
                        ->first();

                    if (!$inventarioAlmacen) {
                        throw new \Exception("No se encontró inventario para el producto '{$modelo->nombre}' en el almacén seleccionado.");
                    }

                    if ($inventarioAlmacen->cantidad < $item['cantidad']) {
                        throw new \Exception("Stock insuficiente en almacén para '{$modelo->nombre}'. Disponible: {$inventarioAlmacen->cantidad}, Solicitado: {$item['cantidad']}");
                    }

                    // Reducir stock del almacén específico
                    $inventarioAlmacen->decrement('cantidad', $item['cantidad']);

                    // También reducir el stock total del producto
                    $modelo->decrement('stock', $item['cantidad']);

                    Log::info("Stock reducido para producto {$modelo->id} en almacén {$validated['almacen_id']}", [
                        'producto_id' => $modelo->id,
                        'almacen_id' => $validated['almacen_id'],
                        'cantidad_reducida' => $item['cantidad'],
                        'stock_almacen_anterior' => $inventarioAlmacen->cantidad + $item['cantidad'],
                        'stock_almacen_actual' => $inventarioAlmacen->cantidad,
                        'stock_total_anterior' => $modelo->stock + $item['cantidad'],
                        'stock_total_actual' => $modelo->stock,
                    ]);
                }
            }

            // Crear entrega de dinero con política por método (auto 'recibido' si transferencia)
            EntregaDineroService::crearAutoPorMetodo(
                'venta',
                $venta->id,
                (float) $venta->total,
                $request->metodo_pago,
                $venta->fecha_pago?->format('Y-m-d') ?? now()->toDateString(),
                (int) $request->user()->id,
                'Entrega automática - Venta #' . ($venta->numero_venta ?? $venta->id) . ' - Método: ' . $request->metodo_pago
            );

            DB::commit();

