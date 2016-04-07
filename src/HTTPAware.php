<?php

namespace Rig\HTTP;

interface HTTPAware
{
    public function setRequest($request);
    public function setResponse($response);
    public function getRequest();
    public function getResponse();
}
