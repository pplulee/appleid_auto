<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Knowledge;
use App\Models\User;
use App\Services\UserService;
use App\Utils\Helper;
use Exception;
use Illuminate\Http\Request;

class KnowledgeController extends Controller
{
    // 在使用文档中显示共享AppleID
    // 请查看 https://docs.appleidauto.org/api/v2board
    // 分享页密码若没有请留空
    // 前端变量 {{apple_idX}} {{apple_pwX}} {{apple_statusX}} {{apple_timeX}}  X为从0开始的数字序号
    private $share_url = "https://test.com/shareapi/kfcv50";

    public function fetch(Request $request)
    {
        if ($request->input('id')) {
            $knowledge = Knowledge::where('id', $request->input('id'))
                ->where('show', 1)
                ->first()
                ->toArray();
            if (!$knowledge) abort(500, __('Article does not exist'));
            $user = User::find($request->user['id']);
            $userService = new UserService();
            if (!$userService->isAvailable($user)) {
                $this->formatAccessData($knowledge['body']);
            }
            $subscribeUrl = Helper::getSubscribeUrl("/api/v1/client/subscribe?token={$user['token']}");
            $knowledge['body'] = str_replace('{{siteName}}', config('v2board.app_name', 'V2Board'), $knowledge['body']);
            $knowledge['body'] = str_replace('{{subscribeUrl}}', $subscribeUrl, $knowledge['body']);
            $knowledge['body'] = str_replace('{{urlEncodeSubscribeUrl}}', urlencode($subscribeUrl), $knowledge['body']);
            $knowledge['body'] = str_replace(
                '{{safeBase64SubscribeUrl}}',
                str_replace(
                    array('+', '/', '='),
                    array('-', '_', ''),
                    base64_encode($subscribeUrl)
                ),
                $knowledge['body']
            );
            $this->apple($knowledge['body']);
            return response([
                'data' => $knowledge
            ]);
        }
        $builder = Knowledge::select(['id', 'category', 'title', 'updated_at'])
            ->where('language', $request->input('language'))
            ->where('show', 1)
            ->orderBy('sort', 'ASC');
        $keyword = $request->input('keyword');
        if ($keyword) {
            $builder = $builder->where(function ($query) use ($keyword) {
                $query->where('title', 'LIKE', "%{$keyword}%")
                    ->orWhere('body', 'LIKE', "%{$keyword}%");
            });
        }

        $knowledges = $builder->get()
            ->groupBy('category');
        return response([
            'data' => $knowledges
        ]);
    }

    private function formatAccessData(&$body)
    {
        function getBetween($input, $start, $end)
        {
            $substr = substr($input, strlen($start) + strpos($input, $start), (strlen($input) - strpos($input, $end)) * (-1));
            return $start . $substr . $end;
        }

        while (strpos($body, '<!--access start-->') !== false) {
            $accessData = getBetween($body, '<!--access start-->', '<!--access end-->');
            if ($accessData) {
                $body = str_replace($accessData, '<div class="v2board-no-access">' . __('You must have a valid subscription to view content in this area') . '</div>', $body);
            }
        }
    }

    private function apple(&$body)
    {
        try {
            $stream_opts = [
                "ssl" => [
                    "verify_peer" => false,
                    "verify_peer_name" => false,
                ],
                "http" => [
                    'timeout' => 5,
                    "header" => [
                        "Content-Type: application/json",
                        "Accept: application/json, text/plain, */*"
                    ]
                ]
            ];
            $result = file_get_contents($this->share_url, false, stream_context_create($stream_opts));
            if ($result === false) {
                throw new Exception("获取失败,页面请求时出现错误");
            }
            $req = json_decode($result, true);
            if (json_last_error() != JSON_ERROR_NONE) {
                throw new Exception("获取失败,JSON数据解析错误,请检查是否为shareapi");
            }
            if ($req["status"]) {
                $accounts = $req["accounts"];
                for ($i = 0; $i < sizeof($accounts); $i++) {
                    $body = str_replace("{{apple_id$i}}", $accounts[$i]["username"], $body);
                    $body = str_replace("{{apple_pw$i}}", $accounts[$i]["password"], $body);
                    $body = str_replace("{{apple_status$i}}", $accounts[$i]["status"] ? "正常" : "异常", $body);
                    $body = str_replace("{{apple_time$i}}", $accounts[$i]["last_check"], $body);
                }
            } else {
                $body = str_replace("{{apple_id0}}", "获取失败,{$req["msg"]}", $body);
            }
        } catch (Exception $error) {
            $body = str_replace("{{apple_id0}}", $error, $body);
        }
    }
}
