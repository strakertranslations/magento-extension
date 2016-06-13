<?php

namespace Straker\EasyTranslationPlatform\Api\Data;

interface StrakerAPIInterface
{

    public function getCountries();

    public function callRegister($data);

    public function saveAppKey($appKey);

    public function saveAccessToken($accessToken);

}