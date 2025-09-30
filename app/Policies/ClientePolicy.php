<?php

namespace App\Policies;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ClientePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Todos los usuarios autenticados pueden ver la lista de clientes
        return $user !== null;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Cliente $cliente): bool
    {
        // Todos los usuarios autenticados pueden ver detalles de clientes
        return $user !== null;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Solo usuarios con permisos específicos pueden crear clientes
        // Por ahora, todos los usuarios autenticados pueden crear
        // Esto se puede ajustar según roles específicos
        return $user !== null;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Cliente $cliente): bool
    {
        // Todos los usuarios autenticados pueden editar clientes
        // Se puede agregar lógica adicional según roles
        return $user !== null;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Cliente $cliente): bool
    {
        // Solo administradores pueden eliminar clientes permanentemente
        // Se puede ajustar según roles específicos
        return $user !== null && (
            $user->hasRole('admin') ||
            $user->hasRole('super-admin') ||
            $user->id === 1 // Usuario root
        );
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Cliente $cliente): bool
    {
        // Solo administradores pueden restaurar clientes eliminados
        return $user !== null && (
            $user->hasRole('admin') ||
            $user->hasRole('super-admin') ||
            $user->id === 1
        );
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Cliente $cliente): bool
    {
        // Solo super-admin puede eliminar permanentemente
        return $user !== null && (
            $user->hasRole('super-admin') ||
            $user->id === 1
        );
    }

    /**
     * Determine whether the user can toggle client status.
     */
    public function toggle(User $user, Cliente $cliente): bool
    {
        // Todos los usuarios autenticados pueden cambiar estado
        return $user !== null;
    }

    /**
     * Determine whether the user can export clients.
     */
    public function export(User $user): bool
    {
        // Solo usuarios con permisos específicos pueden exportar
        return $user !== null;
    }

    /**
     * Determine whether the user can view client statistics.
     */
    public function stats(User $user): bool
    {
        // Todos los usuarios autenticados pueden ver estadísticas
        return $user !== null;
    }
}
