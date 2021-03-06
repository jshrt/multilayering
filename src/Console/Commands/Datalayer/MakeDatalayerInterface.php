<?php

namespace Hamzaouaghad\Multilayering\Console\Commands\Datalayer;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Filesystem\Filesystem;
use Hamzaouaghad\Multilayering\Console\Commands\Common as Common;

class MakeDatalayerInterface extends GeneratorCommand
{
    use Common;

    protected $type = "Interface has been created!";
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:datalayer:interface {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command generates an interface. If no path is specified with the name, this command will put the file in the default datalayer\objects directory.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct($files);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        return $this->fire();
    }

    public function fire()
    {
        $name = $this->parseName($this->getNameInput());
        $name = $this->editName($name);

        $aliasLoader = "\t\t\t\t$" . "loader->alias('" .$this->shortenName($name). "', '" . "$name" . "');";
        $aliasLoader = "//DummyAliasLoadingForInterfaces\n".$aliasLoader;

        if ($this->files->exists($path = $this->getPath($name))) {
            $this->error($this->type.' already exists!');

            return false;
        }

        $this->makeDirectory($path);

        $this->files->put($path, $this->buildClass($name));

        $this->info($this->type.' created successfully.'.$name.'.php');

        $this->updateProvider('//DummyAliasLoadingForInterfaces', $aliasLoader);
    }

    protected function editName($name)
    {
        return str_replace("App", "App\Datalayer\Contracts", $name.'Interface');
    }

    protected function replaceClass($stub, $name)
    {
        $class = str_replace($this->getNamespace($name).'\\', '', $name);

        return str_replace('DummyInterface', $class, $stub);
    }

    public function getStub()
    {
        return __DIR__.'/stubs/Interface.stub';
    }
}