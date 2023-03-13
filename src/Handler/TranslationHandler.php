<?php

namespace Abdallah\LaravelTranslate\Handler;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use App\Exceptions\TranslationHandelerException;
use Illuminate\Support\Collection;
use Abdallah\LaravelTranslate\FileSystem\FileSystem;

class TranslationHandler

{
    //main locale "return array keys only"
    public string $mainLocale;

    //suported locales for handeling
    public array $supportedLocales;

    //translation directoy that translation will put into
    public string $translationsPath;

    //sorce pathas that compiler will search from
    public array $sourcePaths;

    //all files that we will search on
    public array $targetFiles;

    //all keys of the files
    public array $keys;

    //all domains we need
    public array $domains;

    //file system class
    public FileSystem $fileSystem;

    /**
     * int handeler options
     */
    public function __construct()
    {
        //get main data from config
        $this->mainLocale = config('translationHandler.mainLocale', 'en');
        $this->supportedLocales = config('translationHandler.supportedLocales', ['en']);
        $this->translationsPath = base_path() . config('translationHandler.translationsPath', '/resourses/lang');
        $this->sourcePaths = config('translationHandler.sourcePaths', ['/resources/views', '/app']);

        //create file system object and inject it
        $this->fileSystem = new FileSystem();
    }




    /**
     * get keys from all target files
     */
    protected function getAllKeys(): array
    {
        $keys = array();
        //find all data keys in all files 
        foreach ($this->targetFiles as $file) {
            if (file_exists($file)) {
                $content = file_get_contents($file);
                $fileKeysAll = array();
                preg_match_all("/(__\('[a-zA-Z]*.[a-zA-Z 0-9 & , ( ) + -]*'\))/", $content, $fileKeysAll);
                foreach ($fileKeysAll as $fileKeys) {
                    foreach ($fileKeys as $key) {
                        $key = str_replace(["__('", "')"], "", $key);
                        array_push($keys, $key);
                    }
                }

                preg_match_all("/(__\(\"[a-zA-Z]*.[a-zA-Z 0-9 & , ( ) + -]*\"\))/", $content, $fileKeysAll);
                foreach ($fileKeysAll as $fileKeys) {
                    foreach ($fileKeys as $key) {
                        $key = str_replace(["__(\"", "\")"], "", $key);
                        array_push($keys, $key);
                    }
                }
            }
        }

        //remove repeated data
        $keys = array_unique($keys);


        // convert data to domains and key
        $array = collect([]);
        foreach ($keys as $key) {
            $data = explode('.', $key);
            if (count($data) == 2) {
                //if data has domain name
                $array->add(
                    [
                        "domain" => $data[0],
                        "key" => $data[1]
                    ]
                );
            } else {
                //if data has no domain name add to default domain
                $array->add(
                    [
                        "domain" => "default",
                        "key" => $data[0]
                    ]
                );
            }
        }

        return $array->toArray();
    }

    /**
     * get all allaw domains
     */
    protected function getAllDomains(): array
    {
        $keys = collect($this->keys);
        $domains = $keys->map(function ($item, $key) {
            return $item['domain'];
        })->unique();
        return $domains->toArray();
    }

    /**
     * main function that crate translations
     */
    public function updateTranslations(): array
    {
        //get src files to loop for
        $this->targetFiles = $this->fileSystem->getSourceFoldersfiles($this->sourcePaths);

        //get keys and domains from target files
        $this->keys = $this->getAllKeys();
        $this->domains = $this->getAllDomains();

        //check language structure
        $this->fileSystem->checkLocalesDirectory($this->supportedLocales, $this->translationsPath);
        $this->fileSystem->checkDomainsFiles($this->supportedLocales, $this->domains, $this->translationsPath);

        // create new translations
        foreach ($this->supportedLocales as  $locale) {
            foreach ($this->domains as  $domain) {
                //generate path for domains
                $path = array(
                    $this->translationsPath,
                    $locale,
                    $domain . '.php'
                );
                $filePath = implode(DIRECTORY_SEPARATOR, $path);

                //get file data from file
                $fileData = include $filePath;
                $domainData = collect($this->keys)->where('domain', $domain);
                foreach ($domainData as $data) {
                    if (!array_key_exists($data['key'], $fileData)) {
                        $fileData[$data['key']] = "";
                    }
                }

                if ($locale == $this->mainLocale) {
                    //return keys only if main language
                    $this->fileSystem->saveTranslateFile($filePath, $fileData, true);
                } else {
                    //return keys and value if not main language
                    $this->fileSystem->saveTranslateFile($filePath, $fileData);
                }
            }
        }

        return [
            "filesCount" => count($this->targetFiles),
            "keysCount" => count($this->keys),
            "domainsCount" => count($this->domains)
        ];
    }
}
