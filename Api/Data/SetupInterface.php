<?php

namespace Straker\EasyTranslationPlatform\Api\Data;

interface SetupInterface
{
    public function saveAppKey($appKey);

    public function saveAccessToken($accessToken);

    public function saveStoreSetup($source_id, $source_language, $destination_id, $destination_language);

}