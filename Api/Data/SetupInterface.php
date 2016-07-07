<?php

namespace Straker\EasyTranslationPlatform\Api\Data;

interface SetupInterface
{
    public function saveAppKey($appKey);

    public function saveAccessToken($accessToken);

    public function saveStoreSetup($storeId, $source_store, $source_language, $destination_id, $destination_language);

    public function saveClientData($data);

}