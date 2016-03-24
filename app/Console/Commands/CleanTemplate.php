<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class CleanTemplate extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'clean:template';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleans the example files out of the project.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        if ($this->option('force')) {
            $db_reset = 'yes';
            $migrations = 'yes';
            $seeds = 'yes';
        } else {
            $db_reset = $this->confirm('Reset database migrations?', true);
            $migrations = $this->confirm('Remove example database migration?',
                true);
            $seeds = $this->confirm('Remove example database seed?', true);
        }
        if ($db_reset) {
            exec('docker-compose run --rm fpm php artisan migrate:reset');
        }
        if ($migrations) {
            $filename = database_path('migrations/2016_03_16_122149_create_quotes_table.php');
            if($this->deleteFile($filename, 'Example migration')) {
                $this->info('Removing example database migration.');
            }
        }
        if ($seeds) {
            $filename = database_path('seeds/QuotesTableSeeder.php');
            if($this->deleteFile($filename, 'Example seed')) {
                $this->info('Removing example database seed.');
            }

            $this->info('Altering DatabaseSeeder file.');
            $fname = database_path('seeds/DatabaseSeeder.php');

            $rows = file($fname);
            $blacklist = "QuotesTableSeeder";

            foreach($rows as $key => $row) {
                if(preg_match("/($blacklist)/", $row)) {
                    unset($rows[$key]);
                }
            }

            file_put_contents($fname, implode('', $rows));
        }
    }

    private function deleteFile($filename, $type)
    {
        if (file_exists($filename)) {
            unlink($filename);
        } else {
            $this->warn("$type already deleted.");
            return false;
        }
        return true;
    }

    protected function getOptions()
    {
        return array(
            array(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force yes.'
            )
        );
    }

}