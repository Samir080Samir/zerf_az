<?php

namespace App\Console\Commands;

use CoreHelper;
use Illuminate\Console\Command;

class CreateJsonTranslates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'json-translate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate json translates from blade';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        CoreHelper::createLangFile(CoreHelper::findLangStringsInBlade('templates/default'));
        $this->line('Translates Json Created From Resources Lang');
    }
}
