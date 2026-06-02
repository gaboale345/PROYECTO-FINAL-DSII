<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class BackupDatabaseCommand extends Command
{
    protected $signature = 'scsp:backup-database';

    protected $description = 'Respaldo automático de la base de datos cada 6 horas';

    public function handle(): int
    {
        $path = config('scsp.backup.path', storage_path('app/backups'));
        File::ensureDirectoryExists($path);

        $filename = 'backup_'.now()->format('Y-m-d_H-i-s').'.sql';
        $fullPath = $path.DIRECTORY_SEPARATOR.$filename;

        $connection = config('database.default');
        $database = config("database.connections.{$connection}.database");
        $host = config("database.connections.{$connection}.host", '127.0.0.1');
        $user = config("database.connections.{$connection}.username", 'root');
        $password = config("database.connections.{$connection}.password", '');

        $passwordArg = $password !== '' ? '-p'.escapeshellarg($password) : '';
        $cmd = sprintf(
            'mysqldump -h%s -u%s %s %s > %s 2>&1',
            escapeshellarg($host),
            escapeshellarg($user),
            $passwordArg,
            escapeshellarg($database),
            escapeshellarg($fullPath)
        );

        exec($cmd, $output, $exitCode);

        $estado = ($exitCode === 0 && File::exists($fullPath)) ? 'completado' : 'fallido';
        $tamano = File::exists($fullPath) ? File::size($fullPath) : 0;

        DB::table('backups_sistema')->insert([
            'tipo_backup' => 'completo',
            'ruta_archivo' => $fullPath,
            'tamano_bytes' => $tamano,
            'estado' => $estado,
            'fecha_inicio' => now(),
            'fecha_fin' => now(),
            'detalles' => $estado === 'fallido' ? implode("\n", $output) : null,
        ]);

        $this->info("Backup {$estado}: {$fullPath}");

        return $estado === 'completado' ? self::SUCCESS : self::FAILURE;
    }
}
