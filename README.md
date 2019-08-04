
# Duden WebScrapper

```
composer require marciodojr/duden-webscrapper
```

## Example


### Dictionary search

```php

$numberOfAttemptsBeforeConnectionException = 3;
$ws = new DudenWebScrapper($numberOfAttemptsBeforeConnectionException);

// search word (Stichwort)
$words = $ws->dictionarySearch('Stamm');
print_r($words);

/*
(
    [0] => Stamm
    [1] => stammen
    [2] => abstammen
    [3] => herstammen
    [4] => stammhaft
    [5] => Stammfuehrer
    [6] => stammverwandt
    [7] => stammbuertig
    [8] => Stammeltern
    [9] => Stammmorphem
)
*/

```

### Word Orthography

```php


$numberOfAttemptsBeforeConnectionException = 3;
$ws = new DudenWebScrapper($numberOfAttemptsBeforeConnectionException);

// get orthography (Rechtschreibung)
$orthography = $ws->getWordInfo('Apfel');


print_r($orthography);


/*
(
    [lemma] => Ap­fel
    [lemma_determiner] => der
    [word_type] => Substantiv
    [word_gender] => maskulin
    [hyphenation] => Ap|fel
    [meaning] => Array
        (
            [0] => Array
                (
                    [text] => rundliche, fest-fleischige, aromatisch schmeckende Frucht mit Kerngehäuse; Frucht des Apfelbaums
                    [figure] => https://cdn.duden.de/_media_/full/A/Apfel-201020037492.jpg
                    [notes] => Array
                        (
                            [0] => Array
                                (
                                    [title] => Beispiele
                                    [items] => Array
                                        (
                                            [0] => ein grüner, saurer, wurmstichiger, rotbäckiger, gebratener Apfel
                                            [1] => Apfel im Schlafrock
                                            [2] => Äpfel pflücken, [vom Baum] schütteln, schälen, reiben
                                        )
                                )
                            [1] => Array
                                (
                                    [title] => Wendungen, Redensarten, Sprichwörter
                                    [items] => Array
                                        (
                                            [0] => Äpfel und Birnen zusammenzählen, Äpfel mit Birnen vergleichen (umgangssprachlich: Unvereinbares zusammenbringen)
                                            [1] => für einen Apfel und ein Ei (umgangssprachlich: sehr billig, für einen unbedeutenden Betrag: etwas für einen Apfel und ein Ei kriegen)
                                            [2] => in den sauren Apfel beißen (umgangssprachlich: etwas Unangenehmes notgedrungen tun)
                                            [3] => der Apfel fällt nicht weit vom Stamm/nicht weit vom Pferd (jemand ist in seinen [negativen] Anlagen, in seinem Verhalten den Eltern sehr ähnlich)
                                        )
                                )
                        )
                )
            [1] => Array
                (
                    [text] => Apfelbaum
                    [figure] => https://cdn.duden.de/_media_/full/A/Apfel-201020043738.jpg
                    [notes] => Array
                        (
                            [0] => Array
                                (
                                    [title] => Beispiel
                                    [items] => Array
                                        (
                                            [0] => die Äpfel blühen dieses Jahr spät
                                        )
                                )
                            [1] => Array
                                (
                                    [title] => Beispiel
                                    [items] => Array
                                        (
                                            [0] => dies ist ein früher Apfel
                                        )
                                )
                        )
                )
            [2] => Array
                (
                    [text] => Brüste
                    [figure] =>
                    [notes] => Array
                        (
                        )

                )

        )

)
*/
```


## Todo

Parse orthography better


## Testing

```sh
vendor/bin/phpunit --testsuite unit

```