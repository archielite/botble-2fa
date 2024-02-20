<?php

namespace ArchiElite\TwoFactorAuthentication\Http\Controllers;

use ArchiElite\TwoFactorAuthentication\Actions\CreateTwoFactorRecord;
use ArchiElite\TwoFactorAuthentication\Actions\GenerateTwoFactorQrCodeSvg;
use ArchiElite\TwoFactorAuthentication\Actions\GenerateTwoFactorQrCodeUrl;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class TwoFactorQrCodeController extends BaseController
{
    public function show(
        Request $request,
        CreateTwoFactorRecord $createTwoFactorRecord,
        GenerateTwoFactorQrCodeUrl $generateTwoFactorQrCodeUrl,
        GenerateTwoFactorQrCodeSvg $generateTwoFactorQrCodeSvg
    ): BaseHttpResponse {
        $secret = $createTwoFactorRecord($request->user());

        if (empty($secret)) {
            return $this->httpResponse();
        }

        $qrCodeUrl = $generateTwoFactorQrCodeUrl($request->user(), $secret);
        $qrCodeSvg = $generateTwoFactorQrCodeSvg($qrCodeUrl);

        return $this
            ->httpResponse()
            ->setData([
                'svg' => $qrCodeSvg,
                'url' => $qrCodeUrl,
                'secret' => Crypt::decrypt($secret),
            ]);
    }
}
