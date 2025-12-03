<?php

namespace ArchiElite\TwoFactorAuthentication\Http\Controllers;

use ArchiElite\TwoFactorAuthentication\Actions\CreateTwoFactorRecord;
use ArchiElite\TwoFactorAuthentication\Actions\GenerateTwoFactorQrCodeSvg;
use ArchiElite\TwoFactorAuthentication\Actions\GenerateTwoFactorQrCodeUrl;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Illuminate\Support\Facades\Crypt;

class CustomerTwoFactorQrCodeController extends BaseCustomerTwoFactorController
{
    public function show(
        CreateTwoFactorRecord $createTwoFactorRecord,
        GenerateTwoFactorQrCodeUrl $generateTwoFactorQrCodeUrl,
        GenerateTwoFactorQrCodeSvg $generateTwoFactorQrCodeSvg
    ): BaseHttpResponse {
        $customer = auth('customer')->user();
        $secret = $createTwoFactorRecord($customer);

        if (empty($secret)) {
            return $this->httpResponse();
        }

        $qrCodeUrl = $generateTwoFactorQrCodeUrl($customer, $secret);
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
