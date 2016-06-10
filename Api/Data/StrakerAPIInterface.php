<?php

namespace Straker\EasyTranslationPlatform\Api\Data;

interface StrakerAPIInterface
{

    public function getCountries();

    public function callRegister($data);

}