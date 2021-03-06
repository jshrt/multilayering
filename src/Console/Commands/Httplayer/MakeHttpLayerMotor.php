<?php

namespace Hamzaouaghad\Multilayering\Console\Commands\Httplayer;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use InputOption;

use Hamzaouaghad\Multilayering\Console\Commands\Common as Common;

class MakeHttpLayerMotor extends GeneratorCommand
{

    use Common;

    /**
     *
     *
     * @var string
     */
    protected $type    = 'Motor';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:httplayer:motor {name : The name of the motor} 
                                                {--trait= : The trait that is desired to be used}
                                                 {--repository= : A specific repository to be implemented} 
                                                 ';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command generates a motor for the specified class name. If specified, the repository will also be generated for that motor.';

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
        $name      = $this->parseName($this->getNameInput());
        $fileName = $this->editName($name);

        $aliasLoader = "\t\t\t\t$" . "loader->alias('" .$this->shortenName($fileName). "', '" . "$fileName" . "');";
        $aliasLoader = "//DummyAliasLoadingForMotors\n".$aliasLoader;

        if ($this->files->exists($path = $this->getPath($fileName))) {
            $this->error($this->type.' already exists!');

            return false;
        }

        $this->makeDirectory($path);

        $this->files->put($path, $this->buildClass($name));

        $this->info($this->type.' created successfully. '.$fileName.'.php');

        $this->updateProvider('//DummyAliasLoadingForMotors', $aliasLoader);
    }

    protected function editName($name)
    {
        return str_replace("App", "App\Http\Motors", $name.'Motor');
    }


    protected function buildClass($name)
    {
        $isTraitPresent     = false;
        $isInterfacePresent = false;
        
        $stub          = $this->files->get($this->getStub());
        $className     = $this->parseMotor($this->getMotorInput());
        $motor         = $className.'Motor';
        $repoInterface = $className.'RepoInterface';

        if( !is_null( $trait = $this->getTraitInput() ) )
        {
            $isTraitPresent = true;
            $stub           = $this->files->get($this->getStubWithTrait());
        }

        if( !is_null( $repoInterface = $this->getRepoInterfaceInput() ) )
        {
            $isInterfacePresent = true;
        }

        if(!$isInterfacePresent)
        {
            if(!$isTraitPresent)
            {
                //This is the stub where there is no line for a trait use;
                $stub              = $this->files->get($this->getStub());
                return $this->replaceNamespace($stub, $name)->replaceMotor( $this->replaceRepoInterface($this->replaceView($stub, $className), $repoInterface), $motor);
            }   
            return $this->replaceNamespace($stub, $name)->replaceMotor( $this->replaceRepoInterface($this->replaceTrait($this->replaceView($stub, $className), $trait.'Trait'), $motor), $motor);
        }

       return $this->replaceNamespace($stub, $name)->replaceMotor( $this->replaceRepoInterface($this->replaceTrait($this->replaceView($stub, $className), $trait.'Trait'), $repoInterface.'RepoInterface'), $motor);

    }

    /*
     |
     | Stubs editing and correction.
     |
     */

    protected function replaceRepoInterface($stub, $name)
    {
        $repoInterface  = str_replace($this->getNamespace($name).'\\', '', $name);

        return str_replace('DummyRepoInterface', $repoInterface, $stub);
    }


    protected function replaceMotor($stub, $name)
    {
        $motor     = str_replace($this->getNamespace($name).'\\', '', $name);

        return str_replace('DummyMotor', $motor, $stub);
    }


    protected function replaceTrait($stub, $name)
    {
        $trait     = str_replace($this->getNamespace($name).'\\', '', $name);

        return str_replace('DummyTrait', $trait, $stub);
    }

    protected function replaceView($stub, $name)
    {
        $motor     = str_replace($this->getNamespace($name).'\\', '', $name);
        
        return str_replace('view', $motor, $stub);
    }

    /*
     |
     | Stubs loading.
     |
     */

    protected function getStub()
    {
        return __DIR__.'/stubs/ConcreteMotor.stub';
    }

    protected function getStubWithTrait()
    {
        return __DIR__.'/stubs/ConcreteMotorWithTrait.stub';
    }


    /*
     |
     |  Parsing
     |
     */
    protected function parseInterface($name)
    {
        $rootNamespace = $this->laravel->getNamespace();

        if (Str::startsWith($name, $rootNamespace)) {
            return $name;
        }

        if (Str::contains($name, '/')) {
            $name = str_replace('/', '\\', $name);
        }

        return $this->parseName($this->getDefaultNamespace(trim($rootNamespace, '\\')).'\\'.$name);
    }



    protected function parseMotor($name)
    {
        $rootNamespace = $this->laravel->getNamespace();

        if (Str::startsWith($name, $rootNamespace)) {
            return $name;
        }

        if (Str::contains($name, '/')) {
            $name = str_replace('/', '\\', $name);
        }

        return $this->parseName($this->getDefaultNamespace(trim($rootNamespace, '\\')).'\\'.$name);
    }

    /*
     |
     | Input handling
     |
     */

    protected function getTraitInput()
    {
        return $this->option('trait');
    }

    protected function getRepoInterfaceInput()
    {
        return $this->option('repository');
    }

    protected function getMotorInput()
    {
        return $this->argument('name');
    }
}