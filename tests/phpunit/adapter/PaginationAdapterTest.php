<?php

namespace brendt\stitcher\tests\adapter;

use brendt\stitcher\adapter\PaginationAdapter;
use brendt\stitcher\Config;
use brendt\stitcher\site\Page;
use \PHPUnit_Framework_TestCase;

class PaginationAdapterTest extends PHPUnit_Framework_TestCase {

    public function setUp() {
        Config::load('./tests');
    }

    private function createAdapter() {
        return new PaginationAdapter();
    }

    private function createPage() {
        $page = new Page('/entries', [
            'template' => 'home',
            'data'     => [
                'entries' => 'pagination_entries.yml',
            ],
            'adapters' => [
                'pagination' => [
                    'variable' => 'entries',
                    'amount'   => 5,
                ],
            ],
        ]);

        return $page;
    }

    public function test_pagination_adapter() {
        $page = $this->createPage();
        $adapter = $this->createAdapter();

        $result = $adapter->transform($page);

        $this->assertArrayHasKey('/entries/page-1', $result);
        $this->assertArrayHasKey('/entries/page-2', $result);
        $this->assertArrayHasKey('/entries', $result);
    }

    public function test_pagination_adapter_results_per_page() {
        $page = $this->createPage();
        $adapter = $this->createAdapter();

        $result = $adapter->transform($page);

        /** @var Page $page */
        foreach ($result as $page) {
            $this->assertNotEquals(0, count($page->getVariable('entries')));
        }

        $this->assertArrayHasKey('entry-a', $result['/entries/page-1']->getVariable('entries'));
        $this->assertArrayHasKey('entry-f', $result['/entries/page-2']->getVariable('entries'));
        $this->assertArrayHasKey('entry-a', $result['/entries']->getVariable('entries'));
    }

    public function test_pagination_adapter_filtered() {
        $page = $this->createPage();
        $adapter = $this->createAdapter();

        $result = $adapter->transform($page, 2);

        $this->assertArrayHasKey('/entries/page-2', $result);
        $this->assertArrayNotHasKey('/entries/page-1', $result);
    }

    public function test_pagination_adapter_sets_pagination_variable() {
        $page = $this->createPage();
        $adapter = $this->createAdapter();

        $result = $adapter->transform($page);

        foreach ($result as $page) {
            $pagination = $page->getVariable('pagination');

            $this->assertArrayHasKey('current', $pagination);
            $this->assertArrayHasKey('next', $pagination);
            $this->assertArrayHasKey('previous', $pagination);
            $this->assertArrayHasKey('pages', $pagination);
        }

        $this->assertTrue(isset($result['/entries/page-1']->getVariable('pagination')['next']['url']));
        $this->assertTrue(isset($result['/entries/page-1']->getVariable('pagination')['next']['index']));
        $this->assertNull($result['/entries/page-1']->getVariable('pagination')['previous']);

        $this->assertTrue(isset($result['/entries/page-2']->getVariable('pagination')['previous']['url']));
        $this->assertTrue(isset($result['/entries/page-2']->getVariable('pagination')['previous']['index']));
        $this->assertNull($result['/entries/page-2']->getVariable('pagination')['next']);
    }

    public function test_pagination_adapter_sets_parsed_variables() {
        $page = $this->createPage();
        $adapter = $this->createAdapter();

        $result = $adapter->transform($page);

        foreach ($result as $page) {
            $this->assertTrue($page->isParsedVariable('entries'));
            $this->assertTrue($page->isParsedVariable('pagination'));
        }
    }

}
