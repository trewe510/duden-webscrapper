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

    public function testSearchWordWüsten()
    {
        $ws = $this->createInstance();
        $word = 'wüsten';
        $orthography = $ws->getWordInfo($word);
        $this->assertEquals($word, $orthography['lemma']);
    }

    public function testSearchWordReißen()
    {
        $ws = $this->createInstance();
        $word = 'reißen';
        $orthography = $ws->getWordInfo($word);
        $this->assertEquals($word, $orthography['lemma']);
    }

    public function testSearchVerbPassieren()
    {
        $ws = $this->createInstance();
        $orthography = $ws->getWordInfo('passieren');
        // var_dump($orthography);
        $this->assertArrayHasKey('lemma', $orthography);
    }

    public function testSearchVerbAalen()
    {
        $ws = $this->createInstance();
        $orthography = $ws->getWordInfo('aalen');
        // var_dump($orthography);
        $this->assertArrayHasKey('lemma', $orthography);
    }

    public function testGetWordInfoSubstantiveApfel()
    {
        $ws = $this->createInstance();
        $orthography = $ws->getWordInfo('Apfel');
        // var_dump($orthography);
        $this->assertArrayHasKey('lemma', $orthography);
    }

    public function testGetWordInfoAdjectiveSchwierig()
    {
        $ws = $this->createInstance();
        $orthography = $ws->getWordInfo('schwierig');
        // var_dump($orthography);
        $this->assertArrayHasKey('lemma', $orthography);
    }
    public function testGetWordInfoPrepositionBei()
    {
        $ws = $this->createInstance();
        $orthography = $ws->getWordInfo('bei');
        // var_dump($orthography);
        $this->assertArrayHasKey('lemma', $orthography);
    }

    private function createInstance()
    {
        $ws = new DudenWebScrapper(3);
        return $ws;
    }
}