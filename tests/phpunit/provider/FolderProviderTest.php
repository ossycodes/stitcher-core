<?php

use brendt\stitcher\provider\FolderProvider;

class FolderProviderTest extends PHPUnit_Framework_TestCase {

    protected function createFolderProvider() {
        return new FolderProvider('./tests/src/data');
    }

    public function test_folder_provider_parse() {
        $provider = $this->createFolderProvider();

        $data = $provider->parse('churches/');

        $this->assertArrayHasKey('church-c', $data);
        $this->assertArrayHasKey('church-d', $data);
        $this->assertArrayHasKey('church-e', $data);
    }

}
