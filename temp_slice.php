                ];
            }));

            return response()->json([
                'id' => $venta->id,
                'cliente' => $venta->cliente,
                'items' => $items,
                'total' => $venta->total,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener la venta: ' . $e->getMessage()], 404);
        }
    }

    /**
     * Crea una nueva venta.
     */
    public function store(Request $request)
    {
        try {
            // Validar los datos de entrada
            $validatedData = $request->validate([
                'cliente_id' => 'required|exists:clientes,id',
                'items' => 'required|array',
                'items.*.id' => 'required|integer',
                'items.*.tipo' => 'required|in:producto,servicio',
                'items.*.cantidad' => 'required|integer|min:1',
                'items.*.precio' => 'required|numeric|min:0',
            ]);

            // Crear la venta
            $venta = Venta::create([
                'cliente_id' => $validatedData['cliente_id'],
                'total' => array_sum(array_map(function ($item) {
                    return $item['cantidad'] * $item['precio'];
                }, $validatedData['items'])),
            ]);

            // Asociar productos y servicios
            $productos = [];
            $servicios = [];
            foreach ($validatedData['items'] as $item) {
                if ($item['tipo'] === 'producto') {
                    $productos[$item['id']] = [
                        'cantidad' => $item['cantidad'],
                        'precio' => $item['precio'],
                    ];
                    // Descontar stock
                    $productoModel = \App\Models\Producto::find($item['id']);
                    if ($productoModel) {
                        $this->inventarioService->salida($productoModel, $item['cantidad'], [
                            'motivo' => 'Venta API creada',
                            'referencia' => $venta,
                            'detalles' => [
                                'payload' => $item,
                                'fuente' => 'api.ventas.store',
                            ],
                        ]);
                    }
                } elseif ($item['tipo'] === 'servicio') {
                    $servicios[$item['id']] = [
                        'cantidad' => $item['cantidad'],
                        'precio' => $item['precio'],
                    ];
                }
            }
            $venta->productos()->sync($productos);
            $venta->servicios()->sync($servicios);

            return response()->json($venta->load(['cliente', 'productos', 'servicios']), 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al crear la venta: ' . $e->getMessage()], 400);
        }
    }

    /**
     * Actualiza una venta existente.
     */
    public function update(Request $request, $id)
    {
        try {
            $venta = Venta::with(['productos', 'servicios'])->findOrFail($id);

            // Validar los datos de entrada
            $validatedData = $request->validate([
                'cliente_id' => 'sometimes|exists:clientes,id',
                'items' => 'sometimes|array',
                'items.*.id' => 'required_with:items|integer',
                'items.*.tipo' => 'required_with:items|in:producto,servicio',
                'items.*.cantidad' => 'required_with:items|integer|min:1',
                'items.*.precio' => 'required_with:items|numeric|min:0',
            ]);

            // Revertir stock de productos previos
            foreach ($venta->productos as $producto) {
                $pivot = $venta->productos()->where('producto_id', $producto->id)->first()->pivot;
                $this->inventarioService->entrada($producto, $pivot->cantidad, [
                    'motivo' => 'Actualización de venta API (reversión)',
                    'referencia' => $venta,
                    'detalles' => [
                        'fuente' => 'api.ventas.update',
                        'producto_id' => $producto->id,
                    ],
                ]);
            }

            // Actualizar campos principales
            $updateData = [];
            if (isset($validatedData['cliente_id'])) {
                $updateData['cliente_id'] = $validatedData['cliente_id'];
            }
            if (isset($validatedData['items'])) {
                $updateData['total'] = array_sum(array_map(function ($item) {
                    return $item['cantidad'] * $item['precio'];
                }, $validatedData['items']));
            }
            $venta->update($updateData);

            // Actualizar productos y servicios si se proporcionan
            if (isset($validatedData['items'])) {
