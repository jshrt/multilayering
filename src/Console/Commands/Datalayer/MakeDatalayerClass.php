<?php

namespace edenho\multilayergenerator\Console\Commands\Datalayer;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Filesystem\Filesystem;

class MakeDatalayerClass extends GeneratorCommand
{
    protected $type = 'Eloquent Class <<Object>>';

    private $dirPath = 'edenho\multilayergenerator';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:datalayer:class {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command generates an eloquent class aka model. If no path is specified with the name, this command will put the file in the default datalayer\objects directory.';
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

        if ($this->files->exists($path = $this->getPath($name))) {
            $this->error($this->type.' already exists!');

            return false;
        }

        $this->makeDirectory($path);

        $this->files->put($path, $this->buildClass($name));

        $this->info($this->type.' created successfully.'.$name.'.php');
    }

    protected function editName($name)
    {
        return str_replace("App", "App\Datalayer\Objects", $name);
    }

    protected function getStub()
    {
        return __DIR__.'/stubs/Object.stub';
    }
}