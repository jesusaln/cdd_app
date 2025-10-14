<?php
require_once 'vendor/autoload.php';

use App\Models\User;

try {
    $users = User::all(['id', 'name', 'email']);

    echo "Usuarios encontrados: " . $users->count() . "\n";
    echo "----------------------------------------\n";

    foreach ($users as $user) {
        echo $user->id . ': ' . $user->name . ' (' . $user->email . ')' . "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>