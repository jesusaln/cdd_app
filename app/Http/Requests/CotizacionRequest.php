<?php

namespace App\Http\Requests;

use App\Enums\EstadoCotizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CotizacionRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para realizar esta solicitud.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Cambiar según la lógica de autorización si es necesario
    }

    /**
     * Obtiene las reglas de validación para la solicitud.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'cliente_id' => ['required', 'exists:clientes,id'],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'descuento_general' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'iva' => ['required', 'numeric', 'min:0'],
            'total' => ['required', 'numeric', 'min:0'],
            'productos' => ['required', 'array', 'min:1'],
            'productos.*.id' => ['required', 'integer', 'min:1'],
            'productos.*.tipo' => ['required', Rule::in(['producto', 'servicio'])],
            'productos.*.cantidad' => ['required', 'integer', 'min:1'],
            'productos.*.precio' => ['required', 'numeric', 'min:0'],
            'productos.*.descuento' => ['nullable', 'numeric', 'min:0', 'max:100'],
            // Campos opcionales enviados por el cliente, pero no estrictamente necesarios
            'productos.*.subtotal' => ['sometimes', 'numeric', 'min:0'],
            'productos.*.descuento_monto' => ['sometimes', 'numeric', 'min:0'],
        ];
    }

    /**
     * Mensajes de error personalizados para las reglas de validación.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'cliente_id.required' => 'Debes seleccionar un cliente.',
            'cliente_id.exists' => 'El cliente seleccionado no existe.',
            'subtotal.required' => 'El subtotal es obligatorio.',
            'subtotal.numeric' => 'El subtotal debe ser un valor numérico.',
            'subtotal.min' => 'El subtotal no puede ser negativo.',
            'descuento_general.numeric' => 'El descuento general debe ser un valor numérico.',
            'descuento_general.min' => 'El descuento general no puede ser negativo.',
            'descuento_general.max' => 'El descuento general no puede exceder el 100%.',
            'iva.required' => 'El IVA es obligatorio.',
            'iva.numeric' => 'El IVA debe ser un valor numérico.',
            'iva.min' => 'El IVA no puede ser negativo.',
            'total.required' => 'El total es obligatorio.',
            'total.numeric' => 'El total debe ser un valor numérico.',
            'total.min' => 'El total no puede ser negativo.',
            'productos.required' => 'Debes seleccionar al menos un producto o servicio.',
            'productos.array' => 'Los productos deben enviarse como un arreglo.',
            'productos.min' => 'Debes seleccionar al menos un producto o servicio.',
            'productos.*.id.required' => 'El ID del producto es obligatorio.',
            'productos.*.id.integer' => 'El ID del producto debe ser un número entero.',
            'productos.*.tipo.required' => 'El tipo de producto es obligatorio.',
            'productos.*.tipo.in' => 'El tipo debe ser "producto" o "servicio".',
            'productos.*.cantidad.required' => 'La cantidad del producto es obligatoria.',
            'productos.*.cantidad.integer' => 'La cantidad debe ser un número entero.',
            'productos.*.cantidad.min' => 'La cantidad debe ser al menos 1.',
            'productos.*.precio.required' => 'El precio del producto es obligatorio.',
            'productos.*.precio.numeric' => 'El precio debe ser un valor numérico.',
            'productos.*.precio.min' => 'El precio no puede ser negativo.',
            'productos.*.descuento.numeric' => 'El descuento debe ser un valor numérico.',
            'productos.*.descuento.min' => 'El descuento no puede ser negativo.',
            'productos.*.descuento.max' => 'El descuento no puede exceder el 100%.',
            'productos.*.subtotal.numeric' => 'El subtotal del producto debe ser un valor numérico.',
            'productos.*.subtotal.min' => 'El subtotal del producto no puede ser negativo.',
            'productos.*.descuento_monto.numeric' => 'El monto de descuento debe ser un valor numérico.',
            'productos.*.descuento_monto.min' => 'El monto de descuento no puede ser negativo.',
        ];
    }

    /**
     * Prepara los datos para la validación.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // Convertir cantidades a enteros para cumplir con la regla integer
        if ($this->has('productos')) {
            $productos = $this->input('productos', []);
            $this->merge([
                'productos' => array_map(function ($producto) {
                    $producto['cantidad'] = (int) $producto['cantidad'];
                    return $producto;
                }, $productos),
            ]);
        }
    }
}
