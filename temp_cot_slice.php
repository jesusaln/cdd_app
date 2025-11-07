    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $cotizacion = Cotizacion::findOrFail($id);

        if (!in_array($cotizacion->estado, [EstadoCotizacion::Borrador, EstadoCotizacion::Pendiente, EstadoCotizacion::Aprobada], true)) {
            return Redirect::back()->with('error', 'Solo cotizaciones pendientes pueden ser eliminadas');
        }

        $cotizacion->items()->delete();
        $cotizacion->delete();

        return Redirect::route('cotizaciones.index')
            ->with('success', 'CotizaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n eliminada exitosamente');
    }

    /**
     * Convertir cotizaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n a venta.
     * (Nota: la unificaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n completa con VentaItem/ventable_* la hacemos en el paso #8)
     */
    public function convertirAVenta($id)
    {
        $cotizacion = Cotizacion::with(['cliente', 'items.cotizable'])->findOrFail($id);

        if ($cotizacion->estado !== EstadoCotizacion::Aprobada) {
            return Redirect::back()->with('error', 'Solo cotizaciones aprobadas pueden convertirse a venta');
        }

        DB::beginTransaction();
        try {
            // Import ya declarado arriba: use App\Models\Venta;
            $venta = Venta::create([
                'cliente_id' => $cotizacion->cliente_id,
                'cotizacion_id' => $cotizacion->id,
                'total' => $cotizacion->total,
            ]);

            foreach ($cotizacion->items as $item) {
                $class = $item->cotizable_type;
                $id = $item->cotizable_id;

                if ($class === Producto::class) {
                    $venta->productos()->attach($id, [
                        'cantidad' => $item->cantidad,
                        'precio' => $item->precio,
                    ]);

                    $productoVenta = Producto::find($id);
                    if ($productoVenta) {
                        app(InventarioService::class)->salida($productoVenta, $item->cantidad, [
                            'motivo' => 'ConversiÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n de cotizaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n a venta',
                            'referencia' => $venta,
                            'detalles' => [
                                'cotizacion_id' => $cotizacion->id,
                                'cotizacion_item_id' => $item->id,
                            ],
                        ]);
                    }
                } elseif ($class === Servicio::class) {
                    $venta->servicios()->attach($id, [
                        'cantidad' => $item->cantidad,
                        'precio' => $item->precio,
                    ]);
                }
            }

            DB::commit();
            return Redirect::route('ventas.show', $venta->id)
                ->with('success', 'Venta creada exitosamente a partir de la cotizaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al convertir cotizaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n a venta', ['error' => $e->getMessage()]);
            return Redirect::back()->with('error', 'Error al crear la venta');
        }
    }

    /**
     * Duplicar una cotizaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n.
