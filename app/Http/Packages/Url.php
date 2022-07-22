<?php

namespace App\Http\Packages;

class Url
{
    private $url = '';
    private $params = array();
    private $baseUrl;
    private $shape;


    /**
     * @return mixed
     */
    public function getShape()
    {
        return $this->shape;
    }

    /**
     * @param mixed $shape
     */
    public function setShape($shape): void
    {
        $this->shape = $shape;
    }

    /**
     * @return mixed
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @param mixed $baseUrl
     */
    public function setBaseUrl($baseUrl): void
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @param array $params
     */
    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    /**
     * @param null $params
     */
    public function make($params = null)
    {

        $url_complement = $this->getShape();
        if ($params) {
            $data = $this->fillParamsArray($params);
            $url_complement = $this->getShape() . '?' . $data[0] . '=' . $data[1];
        }

        $this->setUrl("{$this->getBaseUrl()}/{$url_complement}");
        return $this;
    }

    /**
     * @param $params
     * @return array
     */
    protected function fillParamsArray($params)
    {
        $array_params = explode("=", $params);
        return $array_params;
    }
}
