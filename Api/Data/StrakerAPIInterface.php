<?php

namespace Straker\EasyTranslationPlatform\Api\Data;

interface StrakerAPIInterface
{

    public function getCountries();

    public function getLanguages();

    public function callRegister($data);

    public function callTranslate($request);

    public function getLanguageName( $code );

}