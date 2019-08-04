<?php

namespace Mdojr\DudenWebScrapper;

use GuzzleHttp\Client;
use PHPHtmlParser\Dom;
use GuzzleHttp\Exception\ConnectException;

class DudenWebScrapper
{
    const MIN_ATTEMPTS = 1;

    private $httpClient;
    private $numberOfAttempts;

    public function __construct(int $numberOfAttempts)
    {
        $this->configureHttpClient();
        $this->numberOfAttempts = max(self::MIN_ATTEMPTS, $numberOfAttempts);
    }

    public function dictionarySearch(string $word)
    {
        $result = $this->get(Endpoint::DICTIONARY_SEARCH, [
            'query' => [
                'display' => 'page_1',
                'filter' => 'search_api_fulltext',
                'q' => $word
            ]
        ], Formatter::JSON);

        if (!$result) {
            return [];
        }

        return array_map(function ($item) {
            return str_replace(' ' . Endpoint::ORTHOGRAPHY . '/', '', $item->value);
        }, $result);
    }

    public function getWordInfo(string $word)
    {
        $result = $this->get(Endpoint::ORTHOGRAPHY . '/' . $word, [], Formatter::HTML);

        if (!$result) {
            return null;
        }

        $main = $result->find('article');
        $type = $main->find('.tuple__val')[0]->text;
        $typeArr = explode(', ', $type);
        $meanings = $this->parseMeaning($main->find('#bedeutungen ol li'));

        $wordInfo = [
            'lemma' => $main->find('.lemma__main')->text,
            'lemma_determiner' => $main->find('.lemma__determiner')->text,
            'word_type' => $typeArr[0],
            'word_gender' => empty($typeArr[1]) ? null : $typeArr[1],
            'word_gender' => empty($typeArr[1]) ? null : $typeArr[1],
            'hyphenation' => $main->find('#rechtschreibung .tuple__val')[0]->text,
            'meaning' => $meanings,
        ];

        return $wordInfo;
    }

    private function parseMeaning($meaningLis)
    {
        $meanings = [];

        foreach ($meaningLis as $li) {
            $figure = $li->find('figure')[0];
            $notes = $li->find('dl.note');

            $notesArr = [];
            foreach ($notes as $n) {
                $items = [];
                foreach ($n->find('.note__list li') as $item) {
                    $items[] = $item->text;
                }
                $notesArr[] = [
                    'title' => $n->find('.note__title')->text,
                    'items' => $items
                ];
            }

            $meanings[] = [
                'text' => $li->find('.enumeration__text')->text,
                'figure' => $figure ? $figure->find('a')->getAttribute('href') : null,
                'notes' => $notesArr
            ];
        }

        return $meanings;
    }

    private function get(string $uri, array $options, string $format)
    {
        $response = $this->request('GET', $uri, $options);

        if ($response->getStatusCode() == 200) {
            return $this->format($response->getBody()->getContents(), $format);
        }

        return null;
    }

    private function request(string $method, string $uri, array $options)
    {
        $response = null;

        for ($attempt = 0; $attempt < $this->numberOfAttempts; $attempt++) {
            try {
                $response = $this->httpClient->request($method, $uri, $options);
                break;
            } catch (ConnectException $e) {
                throw $e;
            }
        }
        return $response;
    }

    private function format(string $content, string $formatter)
    {
        $formattedContent = null;

        switch ($formatter) {
            case Formatter::JSON:
                $formattedContent = json_decode($content);
                break;
            case Formatter::HTML:
                $formattedContent = $this->parseHtml($content);
                break;

            default:
                $formattedContent = $content;
        }

        return $formattedContent;
    }

    private function parseHtml(string $htmlContent)
    {
        $dom = new Dom();
        $dom->load($htmlContent);
        return $dom->find('body');
    }

    private function configureHttpClient()
    {
        $this->httpClient = new Client([
            'base_uri' => Endpoint::BASE,
            'timeout' => 5.0
        ]);
    }
}
