# levenshtein
Simple calculation project

## Current time resultsk 
On pretty low-end MacBook Air with i5-2557M CPU @ 1.70GHz and 4Gb onboard memory, MacOs Sierra 10.12, PHP 7.0:
```
time php breathalyzer.php 187
187

real	0m1.442s
user	0m1.396s
sys	0m0.038s
```

Almost same results on Ubuntu 15.10 with i5-4460 and plenty of RAM, PHP 5.6:

```
time php breathalyzer.php 187
187

real	0m1.549s
user	0m1.520s
sys	0m0.024s
```
## About solution
I'm using standard PHP array as main storage. where keys are vocabulary words and values - minimal levenshtein lengths to vocabulary (i.e. all words from file have 0 in value). First, i check if word exists in vocabulary - if yes - i just return it's levenshtein length, check works very fast due to PHP array internal structure. Else, i am calculating minimum length for input word and save it to storage for future use (some kind of calculations caching). Also i make series of checks to lower the calculations number.

## Some additional notes
- Why levenshtein? Because it very closely matches the desired specifications, fast and shipped with default PHP library
- Do it have any limitations? Yes, word length limited to 255 symbols, non multibyte - built-in PHP limitations. On test dataset i didn't expirenced any memory problems, but it can affect script on larger sized vocabularies 
- Why Symfony console component? I have tried to write naive single-script solution, but it became messy, so it's anyway needed to be splitted into subclasses and symfony offers nice gain to effort ratio, so i decided to choose it
- Why do i not use PHP7 specific instructions? Because i didn't need them and script will run on all modern PHP versions
- Why do i committed vendor folder? Because i want to ship ready-to-use archive, without additional unnecessary preparations
- Can performance be improved any further? Yes, PHP built-in levenshtein function lacks limitation on length (we don't need to calculate beyond current minimum distance), we can fork to calculate more effectively on multi-processor systems, we can pre-calculate number of letters in vocabulary (with serious memory usage), but all of this methods require much more effort and don't required to achieve desired speed
- XDebug must be disabled (commented in PHP configuration) to achieve same speed results, it affects results heavily, up from 1 second. Script checks it and write information in console 