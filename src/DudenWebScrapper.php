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
            return str_replace(' /suchen/dudenonline/', '', $item->value);
        }, $result);
    }

    public function getWordInfo(string $word)
    {
        $word = $this->convert($word);

        $result = $this->get(Endpoint::ORTHOGRAPHY . '/' . $word, [], Formatter::HTML);

        if (!$result) {
            return null;
        }

        $main = $result->find('article');
        $tuples = $main->find('.tuple');

        $type = $tuples[0]->find('.tuple__val')->text;
        $typeArr = explode(', ', $type);
        $frequency = 0;
        $wordUsage = null;

        $isFrequency = strpos($tuples[1]->find('.tuple__key')->text, 'Häufigkeit') === 0;
        $frequencyPosition = $isFrequency ? 1 : 2;
        if($frequencyPosition == 2) {
            $wordUsage = $tuples[1]->find('.tuple__val')->text;
        }
        $frequency = mb_strlen($tuples[$frequencyPosition]->find('.tuple__val')->find('.shaft__full')->text ?? '');

        $gender = isset($typeArr[1]) ? $typeArr[1] : null;
        $lemma = str_replace('­', '', $main->find('.lemma__main')->text);
        $determiner = $main->find('.lemma__determiner');

        $spelling = $this->parseSpelling($main->find('#rechtschreibung .tuple'));
        $meanings = $this->parseMeaning($main->find('#bedeutungen ol li'));

        $wordInfo = [
            'lemma' => $lemma,
            'lemma_determiner' => count($determiner) > 0 ? $determiner->text : null,
            'word_type' => $typeArr[0],
            'word_usage' => $wordUsage,
            'word_gender' => $gender,
            'frequency' => $frequency,
            'spelling' => $spelling,
            'meaning' => $meanings,
        ];

        return $wordInfo;
    }

    private function convert(string $word)
    {
        $pattern = ['/ä/', '/ö/', '/ü/', '/ß/'];
        $replacement = ['ae', 'oe', 'ue', 'sz'];

        return preg_replace($pattern, $replacement, $word);
    }

    private function parseSpelling($spelling)
    {
        $spellingArr = [];
        foreach ($spelling as $sp) {
            $title = $sp->find('.tuple__key')->text;
            $value = $sp->find('.tuple__val');
            $spellingArr[] = [
                'title' => $title,
                'value' => $title != 'Verwandte Form' ? $value->text : $value->find('a')->text,
            ];
        }

        return $spellingArr;
    }

    private function parseMeaning($meaningLis)
    {
        $meanings = [];
        foreach ($meaningLis as $li) {
            $meaningArray = [];
            $subLis = $li->find('.enumeration__sub-item');
            if (count($subLis) < 1) { // test for subitems. Ex: 2.a), 2.b)
                $meaningArray[] = $this->parseMeaningKernel($li);
            } else {
                foreach ($subLis as $subLi) {
                    $meaningArray[] = $this->parseMeaningKernel($subLi);
                }
            }

            $meanings[] = $meaningArray;
        }

        return $meanings;
    }

    private function parseMeaningKernel($li)
    {
        $parsedFigure = null;
        $figure = $li->find('figure')[0];
        $notes = $li->find('dl.note');
        $tuples = $li->find('dl.tuple');

        $notesArr = count($notes) > 1
            ? $this->parseMeaningKernelNotes($notes)
            : $this->parseMeaningKernelTuples($tuples);


        if ($figure) {
            $parsedFigure = [
                'link' => $figure->find('a')->getAttribute('href'),
                'caption' => $figure->find('.depiction__caption')->text,
            ];
        }

        $en = $li->find('.enumeration__text');

        return  [
            'text' => count($en) ? $en->text : null,
            'figure' => $parsedFigure,
            'notes' => $notesArr
        ];
    }

    private function parseMeaningKernelNotes($notes)
    {
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

        return $notesArr;
    }

    private function parseMeaningKernelTuples($tuples)
    {
        $tupleArr = [];
        foreach ($tuples as $n) {
            $tupleArr[] = [
                'title' => $n->find('.tuple__key')->text,
                'items' => [
                    $n->find('.tuple__val')->text
                ]
            ];
        }

        return $tupleArr;
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
            'timeout' => 15.0
        ]);
    }
}
