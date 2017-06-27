<?php

namespace Bunny\Http;

use Bunny\Http\Response;

/**
 * 渲染器
 */
class Render{

    /**
     * @var string type 支持html,twig模板
     */
    private $type;

    /**
     * @var string 文件根目录
     */
    private $rootDir;

    public function __construct(string $rootDir, string $type = 'html'){
        $this->type = $type;
        $this->rootDir = $rootDir;
    }

    /**
     * 渲染响应对象内容。
     *
     * @param Response 响应对象
     *
     * @return Response 响应对象
     */
    public function parse(Response $response){
        if($response->isApi() || empty($response->getView())){
            //设置headers
            $response->setHeader('Content-Type: application/json; charset=UTF-8');
            //设置用户参数
            echo json_encode($response->getData(), JSON_UNESCAPED_UNICODE);
            return $response->setContent(ob_get_clean());
        }
        $view = $response->getView();
        //适配nginx路径问题
        $view = $this->rootDir.$view;
        $response->addData('PATH_ROOT', $this->rootDir);
        if(!file_exists($view)){
            throw new \Exception('view '.$view.' is not found!', 404);
        }
        if($this->type == 'html'){
            $_name = base64_encode($view);
            $_folder = $this->rootDir.'var/cache/html/';
            $_file = $_folder.$_name;
            !is_dir($_folder) && mkdir($_folder, 0777, true);
            if (!is_file($_file) or @filemtime($view) > @filemtime($_file)) {
                $_html = file_get_contents($view);
                $_html = preg_replace('/<\!\-\-{/', '<?php ', $_html);
                $_html = preg_replace('/\}\-\->/', '?>', $_html);
                $_html = preg_replace('/\{\$([^\}]*)\}/', '<?php echo \$\1 ?>', $_html);
                $_html = preg_replace('/\{(\w+\([^\}]*\))\}/', '<?php echo \1 ?>', $_html);
                file_put_contents($_file, $_html);
            }
            //设置用户参数
            extract($response->getData(), EXTR_SKIP);
            require $_file;
            $response->setContent(ob_get_clean());
        }else if($this->type == 'twig'){
            $splitIndex = strrpos($view, "/");
            $folderName = substr($view, 0, $splitIndex);
            $fileName = substr($view, $splitIndex+1, strlen($view));
            $loader = new Twig_Loader_Filesystem($folderName);
            $twig = new Twig_Environment($loader, array(
                'cache' => 'var/cache/twig',
            ));
            $content = $twig->render($fileName, $response->getData());
            $response->setContent($content);
        }else{
            //设置headers
            $response->setHeader('Content-Type: text/html; charset=UTF-8');
            //设置用户参数
            extract($response->getData(), EXTR_SKIP);
            require $view;
            $response->setContent(ob_get_clean());
        }
        return $response;
    }
}