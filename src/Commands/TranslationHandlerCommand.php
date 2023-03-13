<?php

namespace Abdallah\LaravelTranslate\Commands;

use Illuminate\Console\Command;
use Abdallah\LaravelTranslate\Handler\TranslationHandler;

class TranslationHandlerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'language:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'get non translated keys then add it to translate file';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $this->comment(sprintf("start getting translations from files..."));

            $TC = new TranslationHandler();
            $translate = $TC->updateTranslations();


            $this->info("Finished");
            $this->info("all done " . $translate['filesCount'] . " scaned  and " . $translate['keysCount'] . " key founded and " . $translate['domainsCount'] . " domain founded");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
