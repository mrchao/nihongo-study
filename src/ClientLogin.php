<?php

namespace Devine\NihongoStudy;

use MysqliDb;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

class ClientLogin
{
    private MysqliDb $db;
    public function __construct(MysqliDb $db)
    {
        $this->db = $db;
    }

    public function __invoke(ServerRequest $request, Response $response)
    {
        $nickName = $request->getParam("nickname", "");
        $deviceId = $request->getParam("device_id", "");

        $row = $this->db
            ->where("device_id", $deviceId)
            ->getOne("user");
        $ret = false;
        if($deviceId && $nickName && empty($row)) {
            $ret = $this->db->insert("user", [
                'nickname' => $nickName,
                'device_id' => $deviceId,
                'create_time' => time()
            ]);
        }
        $result = !empty($row) || $ret;
        return $response->withJson([
            "isLogin" => $result,
            "nickName" => $row['nickname'] ?? $nickName,
            "token" => $deviceId
        ]);
    }
}