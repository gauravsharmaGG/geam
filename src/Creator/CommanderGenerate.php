<?php

namespace Goksgreat\Geam\Creator;

use Illuminate\Console\AppNamespaceDetectorTrait;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;

class CommanderGenerate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geam:generate {name : The name of the model} {entities : The name of the entities}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'GEAM Model Generator';


    protected $modelsDir;
    protected $routesDir;
    protected $modelName;
    protected $modelTemplatePath;
    protected $controllerTemplatePath;
    protected $routeTemplatePath;
    protected $controllerName;
    protected $entity = [];
    protected $entitystring;
    protected $controllersDir;
    protected $skipGenerationFlag;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {


    $this->declareVariables($this->argument('name'),$this->argument('entities'));

        $this->createModel();
        $this->createController();
        $this->createRoutes();

    }



    /**
     *  Declare variables for geamGenerator.
     */
    protected function declareVariables($name, $entities)
    {
        $this->modelTemplatePath = base_path(config('geam-creator.geam_creator_model'));
        $this->controllerTemplatePath = base_path(config('geam-creator.geam_creator_controller'));
        $this->routeTemplatePath = base_path(config('geam-creator.geam_creator_route'));
        $this->modelsDir = base_path(config('geam-creator.geam_model_path'));
        $this->controllersDir = base_path(config('geam-creator.geam_controllers_dir'));
        $this->routesDir = base_path(config('geam-creator.geam_route'));
        $this->modelName = ucfirst($name);
        $this->controllerName = ucfirst($name)."Controller";
        $this->entity = explode(',', $entities);
        $this->entitystring = $entities;
        $this->skipGenerationFlag = 'N';
        return $this;
    }

    /**
     *  Create controller class file from geamGenerator.
     */
    protected function createController()
    {
        $this->geamGenerate('controller');
    }

    /**
     *  Create model class file from geamGenerator.
     */
    protected function createModel()
    {
        $this->geamGenerate('model');
    }


    /**
     *  Create route from geamGenerator.
     */
    protected function createRoutes()
    {
        $this->geamGenerate('routes');
    }

    /**
     *  Generate files from the templates.
     */
    protected function geamBuilder($path,$file, $data, $type)
    {
       $template = file_get_contents($file);

       foreach($data as $key => $value)
       {
         $template = str_replace('{'.$key.'}', $value, $template);
       }
       if($type!="route"){ 
        if(! file_exists($path)) {
                 file_put_contents($path, $template);
                 $this->info('Geam Modeller ' . $path .' has been Created Successfully.');
            } else {
                $this->skipGenerationFlag = 'Y';
                 $this->error('Already Exists.');
            }
        }
        else{
            $lines = file($path);
            $lastLine = trim($lines[count($lines) - 1]);

            if (strcmp($lastLine, '});') === 0) {
                $lines[count($lines) - 1] = '    '.$template;
                $lines[] = "\r\n});\r\n";
            } else {
                $lines[] = "$template\r\n";
            }

            $fp = fopen($path, 'w');
            fwrite($fp, implode('', $lines));
            fclose($fp);

            $this->info('Routes added successfully.');
        }
    }

    /**
     *  Generate folder for the files.
     */
    protected function createDirectory($path)
    {
        if (!$this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        }
    }

    protected function geamGenerate($type)
    {
        $modelName = $this->modelName;
        $fields=explode(",", $this->entitystring);
        $fieldsName = "'".implode("','", $fields)."'";
        $controllerName = $this->controllerName;
        $updateParametertemplate = "if(\$request->{^entity^}!=null) \$return->{^entity^} = \$request->{^entity^};";
        $updateParameter = "";
        if ($modelName === '' || is_null($modelName) || empty($modelName)) {
                 $this->error('Name Invalid..!');
             }

        if($type=='model'){
            $file = $this->modelTemplatePath;
            $data  = array('modelName' =>  $this->modelName, 'labelName' =>  $this->modelName, 'fieldsName' =>  $fieldsName);
             $geamModelName = 'app/' . $modelName . '.php';
             $this->geamBuilder($geamModelName,$file, $data, "model");
            }
        if($type=='controller'){
            foreach($fields as $entity)
               {
                 $updateParameter .= str_replace('{^entity^}', $entity, $updateParametertemplate)."\n\t\t";
               }
            $file = $this->controllerTemplatePath;
            $data  = array('controllerName' =>  $this->controllerName,'modelName' =>  $this->modelName, 'updateParameter' => $updateParameter);
             $geamControllerName = $this->controllersDir . $controllerName . '.php';
             if($this->skipGenerationFlag!='Y'){
             $this->geamBuilder($geamControllerName,$file, $data, "controller");
             }
        }
        if($type=='routes'){
            $geamRouteName = 'app/Http/routes.php';
            $file = $this->routeTemplatePath;
            $data  = array('controllerName' =>  $this->controllerName,'modelName' =>  $this->modelName);
            if($this->skipGenerationFlag!='Y'){
            $this->geamBuilder($geamRouteName,$file, $data, "route");
             }   
        }
    }


}
