<?php

namespace Abdallah\LaravelTranslate\FileSystem;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */



class FileSystem

{


    /**
     * check for translation directory
     */
    public function checkLocalesDirectory(array $locales, string $path): void
    {
        //loop for all languages to check
        foreach ($locales as  $locale) {
            //generate path for language
            $targetPath = array(
                $path,
                $locale
            );

            $filePath = implode(DIRECTORY_SEPARATOR, $targetPath);

            //check if path created or not
            if (!file_exists($filePath)) {
                $this->createDirectory($filePath);
            }
        }
    }





    /**
     * check for translation domains
     */
    public function checkDomainsFiles(array $locales, array $domains, string $path): void
    {
        //loop for all languages to check
        foreach ($locales as  $locale) {
            foreach ($domains as  $domain) {
                //generate path for domains
                $targetPath = array(
                    $path,
                    $locale,
                    $domain . '.php'
                );

                $filePath = implode(DIRECTORY_SEPARATOR, $targetPath);

                //check if path created or not
                if (!file_exists($filePath)) {
                    $this->createDomain($filePath);
                }
            }
        }
    }





    /**
     * create directory
     */
    protected function createDirectory(string $filePath): void
    {
        //try to creat path or return error
        if (!file_exists($filePath) && !mkdir($filePath, 0777, true)) {
            throw new \Exception('Can\'t create the directory: %s');
            // throw new TranslationHandelerException(
            //     sprintf('Can\'t create the directory: %s')
            // );
        }
    }





    /**
     * create new domain 
     */
    protected function createDomain(string $filePath): void
    {
        $template = '<?php' . "\n";
        $template .= 'return  [' . "\n";
        $template .= '];' . "\n";

        // File creation
        $file = fopen($filePath, "w+");
        fwrite($file, $template);
        fclose($file);
        chmod($filePath, 0777);
    }




    /**
     * get all floders that we will compile
     */
    public function getSourceFoldersfiles(array $sourcePaths): array
    {
        $paths = array();
        //get paths from array and get its files
        foreach ($sourcePaths as $path) {
            $targetPath = base_path() . $path;
            array_push($paths, ...$this->getFolderPaths($targetPath));
        }
        return $paths;
    }





    /**
     * get all files from folder
     */
    protected function getFolderPaths(string $path): array
    {
        $files = array();

        //scan all dir data
        $elements =  scandir($path);

        //remove routes elements
        $elements = array_diff($elements, ['.', '..']);

        //get data from each element in dire elements
        foreach ($elements as $element) {
            $elementPath = $path . DIRECTORY_SEPARATOR . $element;
            if (is_file($elementPath)) {
                array_push($files, $elementPath);
            } else {
                array_push($files, ...$this->getFolderPaths($elementPath));
            }
        }
        return $files;
    }





    /**
     * safe domain file to target
     */
    public function saveTranslateFile(string $path, array $data, bool $withoutValues = false): void
    {
        if ($withoutValues) {
            $template = '<?php' . "\n";
            $template .= '$array =  [' . "\n";
            foreach ($data as $key => $value) {
                $template .= "    \"$key\", \n";
            }
            $template .= '];' . "\n";
            $template .= 'return array_combine($array, $array);';
        } else {
            $template = '<?php' . "\n";
            $template .= 'return  [' . "\n";
            foreach ($data as $key => $value) {
                $template .= "    \"$key\" => \"$value\", \n";
            }
            $template .= '];' . "\n";
        }
        // File creation
        $file = fopen($path, "w+");
        fwrite($file, $template);
        fclose($file);
    }
}
