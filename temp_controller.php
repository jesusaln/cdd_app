<?php 
  
namespace App\Http\Controllers;  
 
use App\Models\CuentasPorCobrar;  
use App\Models\Venta;  
use App\Enums\EstadoVenta;  
use Illuminate\Http\Request;  
use Illuminate\Support\Facades\DB;  
use Illuminate\Support\Facades\Log;  
use Inertia\Inertia;  
 
class CuentasPorCobrarController extends Controller  
{ 
