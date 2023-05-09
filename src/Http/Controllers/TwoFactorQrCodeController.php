<?php

namespace Botble\TwoFa\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\TwoFa\Actions\CreateTwoFactorRecord;
use Botble\TwoFa\Actions\GenerateTwoFactorQrCodeSvg;
use Botble\TwoFa\Actions\GenerateTwoFactorQrCodeUrl;
use Illuminate\Http\Request;

class TwoFactorQrCodeController extends BaseController
{
    public function show(
        Request $request,
        BaseHttpResponse $response,
        CreateTwoFactorRecord $createTwoFactorRecord,
        GenerateTwoFactorQrCodeUrl $generateTwoFactorQrCodeUrl,
        GenerateTwoFactorQrCodeSvg $generateTwoFactorQrCodeSvg
    ): BaseHttpResponse {
        $secret = $createTwoFactorRecord($request->user());

        if (empty($secret)) {
            return $response;
        }

        $qrCodeUrl = $generateTwoFactorQrCodeUrl($request->user(), $secret);
        $qrCodeSvg = $generateTwoFactorQrCodeSvg($qrCodeUrl);

        return $response->setData([
            'svg' => $qrCodeSvg,
            'url' => $qrCodeUrl,
            'secret' => decrypt($secret),
        ]);
    }
}
