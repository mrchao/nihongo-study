<?php

namespace Devine\NihongoStudy;

use Devine\NihongoStudySdk\Serv\DictServ;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

class MochiToDict
{
    const FILE_PATH = TMP_ROOT . "/word/1.txt";

    public function __invoke(ServerRequest $request, Response $response)
    {
        DictServ::$mdFile = true;
        $dict = new DictServ(self::FILE_PATH);
        $dict = $dict->parse();
        $dict->saveToDb();

        return $response->withJson($dict->dbList);
    }
}