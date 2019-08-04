<?php

namespace Mdojr\DudenWebScrapper\Tests;

use PHPUnit\Framework\TestCase;
use Mdojr\DudenWebScrapper\DudenWebScrapper;

class DudenWebScrapperTest extends TestCase
{
    public function testCanCreateInstance()
    {
        $ws = $this->createInstance();
        $this->assertInstanceOf(DudenWebScrapper::class, $ws);
    }

    public function testSearchWordStamm()
    {
        $ws = $this->createInstance();
        $words = $ws->dictionarySearch('Stamm');
        $this->assertContains('Stamm', $words);
    }

    public function testGetWordInfoApfel()
    {
        $ws = $this->createInstance();
        $orthography = $ws->getWordInfo('Apfel');

        print_r($orthography);

        $this->assertArrayHasKey('lemma', $orthography);
    }

    private function createInstance()
    {
        $ws = new DudenWebScrapper(3);
        return $ws;
    }
}