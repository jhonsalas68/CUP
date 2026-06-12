<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VoiceQueryDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar tablas para evitar duplicados si se corre de nuevo.
        // Eliminamos primero pedidos debido a la relación de clave foránea.
        DB::table('pedidos')->delete();
        DB::table('productos')->delete();
        DB::table('usuarios')->delete();

        // Insertar Usuarios
        $usuarioIds = [];
        $usuarios = [
            ['nombre' => 'Juan Perez', 'email' => 'juan@example.com', 'creado_at' => now()->subDays(10)],
            ['nombre' => 'Maria Lopez', 'email' => 'maria@example.com', 'creado_at' => now()->subDays(5)],
            ['nombre' => 'Carlos Gomez', 'email' => 'carlos@example.com', 'creado_at' => now()->subDays(2)],
            ['nombre' => 'Ana Rodriguez', 'email' => 'ana@example.com', 'creado_at' => now()->subDay()],
        ];

        foreach ($usuarios as $u) {
            $usuarioIds[] = DB::table('usuarios')->insertGetId($u);
        }

        // Insertar Productos
        $productoIds = [];
        $productos = [
            ['nombre' => 'Laptop Gamer', 'precio' => 1200.00, 'stock' => 15],
            ['nombre' => 'Mouse Inalámbrico', 'precio' => 25.50, 'stock' => 150],
            ['nombre' => 'Teclado Mecánico', 'precio' => 80.00, 'stock' => 45],
            ['nombre' => 'Monitor 4K', 'precio' => 350.00, 'stock' => 8],
            ['nombre' => 'Auriculares Cancelación Ruido', 'precio' => 150.00, 'stock' => 25],
        ];

        foreach ($productos as $p) {
            $productoIds[] = DB::table('productos')->insertGetId($p);
        }

        // Insertar Pedidos
        $pedidos = [
            [
                'usuario_id' => $usuarioIds[0], // Juan
                'total' => 1225.50,
                'estado' => 'entregado',
                'fecha' => '2026-06-01',
            ],
            [
                'usuario_id' => $usuarioIds[0], // Juan
                'total' => 80.00,
                'estado' => 'pendiente',
                'fecha' => '2026-06-08',
            ],
            [
                'usuario_id' => $usuarioIds[1], // Maria
                'total' => 350.00,
                'estado' => 'entregado',
                'fecha' => '2026-06-05',
            ],
            [
                'usuario_id' => $usuarioIds[2], // Carlos
                'total' => 175.50,
                'estado' => 'cancelado',
                'fecha' => '2026-06-09',
            ],
        ];

        foreach ($pedidos as $pe) {
            DB::table('pedidos')->insert($pe);
        }
    }
}
