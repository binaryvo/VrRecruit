<?php

namespace Vreasy\Utils;

class Str
{
    protected static $gsmCharacters = [
        '@','Δ','SP','0','¡','P','¿','p','£','_','!',
        '1','A','Q','a','q','$','Φ','\"','2','B','R','b','r',
        '¥','Γ','#','3','C','S','c','s','è','Λ','¤','4','D','T',
        'd','t','é','Ω','%','5','E','U','e','u','ù','Π','&','6',
        'F','V','f','v','ì', 'Ψ', '\'', '7', 'G', 'W', 'g', 'w',
        'ò', 'Σ', '(', '8', 'H', 'X', 'h', 'x', 'Ç', 'Θ', ')','9',
        'I', 'Y', 'i', 'y', 'LF', 'Ξ', '*', ':', 'J', 'Z', 'j', 'z',
        'Ø', 'ESC', '+', ';', 'K', 'Ä', 'k', 'ä', 'ø', 'Æ', ',', '<',
        'L', 'Ö', 'l', 'ö', 'CR', 'æ', '-', '=', 'M', 'Ñ', 'm', 'ñ',
        'Å', 'ß', '.', '>', 'N', 'Ü', 'n', 'ü', 'å', 'É', '/', '?', 'O',
        '§', 'o', 'à'
    ];
    
    protected static $commandWords = ['Ok', 'Yes', 'Yep', 'Si', 'No'];
    
    protected static $confirmCommandWords = ['Ok', 'Yes', 'Yep', 'Si'];
    
    protected static $declineCommandWords = ['No'];

    static public function isGsm($input)
    {
        return in_array($input, self::$gsmCharacters);
    }
    
    static public function checkAndFixWord($wordToCheck)
    {
        $wordToCheck = strtolower($wordToCheck);
        $commandWords = array_map('strtolower', self::$commandWords);
                
        $similarity = 0;
        $metaSimilarity = 0;
        $minLevenshtein = 1000;
        $metaMinLevenshtein = 1000;
    
        $possibleWord = [];
        foreach($commandWords as $n=>$k) {
            if(levenshtein(metaphone($wordToCheck), metaphone($k)) < mb_strlen(metaphone($wordToCheck))/2) {
                if(levenshtein($wordToCheck, $k) < mb_strlen($wordToCheck)/2) {
                    $possibleWord[$n] = $k;
                }
            }
        }
        
        foreach($possibleWord as $n) {
            $minLevenshtein = min($minLevenshtein, levenshtein($n, $wordToCheck));
        }
        
        foreach($possibleWord as $n) {
            if(levenshtein($k, $wordToCheck) == $minLevenshtein) {
                $similarity = max($similarity, similar_text($n, $wordToCheck));
            }
        }
        
        $result = [];
        foreach($possibleWord as $n=>$k) {
            if(levenshtein($k, $wordToCheck) <= $minLevenshtein) {
                if(similar_text($k, $wordToCheck) >= $similarity) {
                  $result[$n] = $k;
                }
            }
        }
        
        foreach($result as $n) {
            $metaMinLevenshtein = min($metaMinLevenshtein, levenshtein(metaphone($n), metaphone($wordToCheck)));
        }

        foreach($result as $n) {
            if(levenshtein($k, $wordToCheck) == $metaMinLevenshtein) {
              $metaSimilarity = max($metaSimilarity, similar_text(metaphone($n), metaphone($wordToCheck)));
            }
        }
        
        $metaResult = [];
        foreach($result as $n=>$k) {
            if(levenshtein(metaphone($k), metaphone($wordToCheck)) <= $metaMinLevenshtein) {
                if(similar_text(metaphone($k), metaphone($wordToCheck)) >= $metaSimilarity) {
                    $metaResult[$n] = $k;
                }
            }
        }

        return (!empty($metaResult) ? current($metaResult) : false);
    }
    
    static private function checkIsWordInHaystack($wordToCheck, $haystack)
    {
        if (in_array(strtolower($wordToCheck), array_map('strtolower', $haystack))) {
            return true;
        } else { // try to fix the word if it is wrong
            if (in_array(strtolower(Str::checkAndFixWord($wordToCheck)), array_map('strtolower', $haystack))) {
                return true;
            }
        }
        return false;
    }

    static public function checkIsConfirmWord($wordToCheck)
    {
        return self::checkIsWordInHaystack($wordToCheck, Str::$confirmCommandWords);
    }
    
    static public function checkIsDeclineWord($wordToCheck)
    {
        return self::checkIsWordInHaystack($wordToCheck, Str::$declineCommandWords);
    }
}
