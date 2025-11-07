                'fecha' => now()->format('Y-m-d'),
                'moneda' => 'MXN'
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validación dinámica para series en ventas
        $rules = [
            'cliente_id' => 'required|exists:clientes,id',
            'almacen_id' => 'required|exists:almacenes,id',
            'vendedor_type' => 'nullable|in:App\\Models\\User,App\\Models\\Tecnico',
            'vendedor_id' => 'nullable|integer',
            'productos' => 'required|array',
            'productos.*.id' => 'required|integer|exists:productos,id',
            'productos.*.tipo' => 'required|in:producto,servicio',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio' => 'required|numeric|min:0',
            'productos.*.descuento' => 'required|numeric|min:0|max:100',
            'descuento_general' => 'nullable|numeric|min:0|max:100',
            'notas' => 'nullable|string',
        ];

        foreach (($request->productos ?? []) as $index => $p) {
            if (($p['tipo'] ?? '') === 'producto') {
                $productoModel = Producto::find($p['id'] ?? null);
                if ($productoModel && ($productoModel->requiere_serie ?? false)) {
                    $requiredSize = isset($p['cantidad']) ? max(1, (int) $p['cantidad']) : 1;
                    $rules["productos.{$index}.seriales"] = ['required', 'array', 'size:' . $requiredSize];
                    $rules["productos.{$index}.seriales.*"] = 'required|string|max:191|distinct';
                }
            }
        }

        $validated = $request->validate($rules);

        DB::beginTransaction();
        try {
            // Validar stock disponible para productos en el almacén seleccionado
            foreach ($validated['productos'] as $item) {
                if ($item['tipo'] === 'producto') {
                    $producto = Producto::find($item['id']);
                    if (!$producto) {
                        return redirect()->back()->with('error', "Producto con ID {$item['id']} no encontrado");
                    }

                    // Obtener stock específico del almacén seleccionado
                    $inventarioAlmacen = \App\Models\Inventario::where('producto_id', $producto->id)
                        ->where('almacen_id', $validated['almacen_id'])
                        ->first();

                    $stockDisponible = $inventarioAlmacen ? $inventarioAlmacen->cantidad : 0;

                    if ($stockDisponible < $item['cantidad']) {
                        $almacen = Almacen::find($validated['almacen_id']);
                        $nombreAlmacen = $almacen ? $almacen->nombre : 'Almacén seleccionado';
                        return redirect()->back()->with(
                            'error',
                            "Stock insuficiente para '{$producto->nombre}' en {$nombreAlmacen}. Disponible: {$stockDisponible}, Solicitado: {$item['cantidad']}"
                        );
                    }
                }
            }

            // Validar mÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡rgenes de ganancia
            $marginService = new MarginService();
            $validacionMargen = $marginService->validarMargenesProductos($validated['productos']);

            if (!$validacionMargen['todos_validos']) {
                // Si hay productos con margen insuficiente, verificar si el usuario aceptÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³ el ajuste
                if ($request->has('ajustar_margen') && $request->ajustar_margen === 'true') {
                    // Ajustar precios automÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡ticamente
                    foreach ($validated['productos'] as &$item) {
                        if ($item['tipo'] === 'producto') {
                            $producto = Producto::find($item['id']);
                            if ($producto) {
                                $item['precio'] = $marginService->ajustarPrecioAlMargen($producto, $item['precio']);
                            }
                        }
                    }
                } else {
                    // Mostrar advertencia y permitir al usuario decidir
                    $mensaje = $marginService->generarMensajeAdvertencia($validacionMargen['productos_bajo_margen']);
                    return redirect()->back()
                        ->withInput()
                        ->with('warning', $mensaje)
                        ->with('requiere_confirmacion_margen', true)
                        ->with('productos_bajo_margen', $validacionMargen['productos_bajo_margen']);
                }
            }

            $subtotal = 0;
            foreach ($validated['productos'] as $item) {
                $subtotalItem = $item['cantidad'] * $item['precio'];
                $subtotal += $subtotalItem;
            }

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

