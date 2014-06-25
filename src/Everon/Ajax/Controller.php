<?php
namespace Everon\Ajax;

use Everon\Http;

/**
 * @method \Everon\Http\Interfaces\Response getResponse()
 */
abstract class Controller extends \Everon\Controller implements Interfaces\Controller
{
    /**
     * @var array
     */
    protected $json_data = null;


    /**
     * @param $action
     * @param $result
     */
    protected function prepareResponse($action, $result)
    {
        $this->getResponse()->setData([
            'result' => $result,
            'data' => $this->json_data
        ]);
    }

    protected function response()
    {
        echo $this->getResponse()->toJson();
    }

    /**
     * @inheritdoc
     */
    public function setJsonData($json_data)
    {
        $this->json_data = $json_data;
    }

    /**
     * @inheritdoc
     */
    public function getJsonData()
    {
        return $this->json_data;
    }
}