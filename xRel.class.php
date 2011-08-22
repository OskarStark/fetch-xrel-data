<?php
class xRel
{

    protected $searchString = null;
    protected $limit = 30;
    protected $sectionVar = null;
    protected $subSectionVar = null;
    protected $resultHTML = null;
    protected $resultList = array();
    protected $searchResults = array();
    protected $searchResult = array();
    protected $releaseResults = array();
    protected $releasesCount = 0;
    protected $tempCount = 0;
    protected $detailHTML = null;
    protected $releasesDetailHTML = null;
    protected $releaseDetailHTML = null;
    protected $resultURL = null;
    protected $releasesLink = null;
    protected $releaseDetailLink = null;
    protected $cookieValues = 'ANON_LOCALE=de_DE';
    protected $pageCharset = null;
    protected $checkUTF8 = false;
    protected $getReleases = true;
    protected $getReleasesLimit = null;
    protected $outputType = 'raw';
    protected $convertEverythingToUTF8 = true;

    public function setSection($sectionVar)
    {
        if ('' == $sectionVar || null == $sectionVar)
        {
            throw new Exception('sectionVar is empty' . __METHOD__);
        }
        $this->sectionVar = $sectionVar;
    }

    public function convertEverythingToUTF8()
    {

    }

    public function setReleasesLimit($limit)
    {
        if ($limit && !is_numeric($limit))
        {
            throw new Exception('Wrong Parameter for releasesLimit in ' . __METHOD__);
        }
        $this->getReleasesLimit = $limit;
    }

    public function setCookieValues($cookieValues)
    {
        $this->cookieValues = $cookieValues;
    }

    public function setSubSection($subSectionVar)
    {
        if ('' == $subSectionVar || null == $subSectionVar)
        {
            throw new Exception('subSectionVar is empty' . __METHOD__);
        }
        $this->subSectionVar = $subSectionVar;
    }

    public function setSearchString($searchString)
    {
        if ('' == $searchString || null == $searchString)
        {
            throw new Exception('SearchString is empty! ' . __METHOD__);
        }

        $this->searchString = $searchString;
    }

    public function setLimit($limit)
    {
        if ($limit && !is_numeric($limit))
        {
            throw new Exception('Wrong Parameter for Limit in ' . __METHOD__);
        }
        $this->limit = $limit;
    }

    public function getSearchParams()
    {
        $data = array();
        $data['searchString'] = $this->searchString;
        $data['sectionVar'] = $this->sectionVar;
        $data['subSectionVar'] = $this->subSectionVar;
        $data['limit'] = $this->limit;
        $data['pageCharset'] = $this->pageCharset;
        $data['checkUTF8'] = ($this->checkUTF8 == true) ? 'Ja' : 'Nein';
        $data['getReleases'] = ($this->getReleases == true) ? 'Ja' : 'Nein';
        $data['releasesLimit'] = $this->getReleasesLimit;

        return $data;
    }

    public function countDoSearch()
    {
        return count($this->resultList);
    }

    public function setCharset($source)
    {
        $charsetPattern = '<\?xml version="1.0" encoding="(.*?)"\?>';

        preg_match_all($charsetPattern, $source, $charset);

        if (isset($charset[1][0]))
        {
            $charset = strtolower($charset[1][0]);
            if ('utf-8' == $charset)
            {
                $this->checkUTF8 = true;
            }
            $this->pageCharset = $charset;
        }
    }

    public function getCharset()
    {
        return $this->pageCharset;
    }

    public function doSearch()
    {
        $searchURL = 'http://www.xrel.to/search.html?xrel_search_query=' . urlencode($this->searchString);

        $this->resultHTML = self::httpGET($searchURL, $this->cookieValues);

        $this->setCharset($this->resultHTML);

        switch ($this->sectionVar)
        {
            case 'movie':
                $pattern = '/<a [^>]*href="(\/movie\/[^">]*)" class="plain">/ism';
                break;
            case 'game':
                switch ($this->subSectionVar)
                {
                    case 'PC':
                        $pattern = '/<a [^>]*href="(\/game\/[^">]*)">/ism';
                        break;
                    case 'Konsole':
                        $pattern = '/<a [^>]*href="(\/console\/[^">]*)">/ism';
                        break;
                    default:
                        throw new Exception('Unknown subSectionVar' . __METHOD__);
                        break;
                }
                break;
            default:
                throw new Exception('Unknown sectionVar' . __METHOD__);
                break;
        }

        preg_match_all($pattern, $this->resultHTML, $matches);

        $this->resultList = array_unique($matches[1]);

        return $this->resultList;
    }

    public function getResult($resultURL)
    {
        if ('' == $resultURL)
        {
            throw new Exception('ResultURL is empty' . __METHOD__);
        }

        $this->resultURL = $resultURL;
        $searchURL = 'http://www.xrel.to' . $this->resultURL;

        $this->detailHTML = self::httpGET($searchURL, $this->cookieValues);

        switch ($this->sectionVar)
        {
            case 'movie':
                $this->searchResult = array(
                    'title' => $this->getTitle(),
                    'originalTitle' => $this->getOriginalTitle(),
                    'coverLink' => $this->getCoverLink(),
                    'runtime' => $this->getRuntime(),
                    'fsk' => $this->getFSK(),
                    'cinemaDE' => $this->getCinemaDE(),
                    'cinemaUS' => $this->getCinemaUS(),
                    'description' => $this->getDescription(),
                    'xrelReleasesLink' => $this->getReleasesLink(),
                    'imdbLink' => $this->getImdbLink(),
                    'productionYear' => $this->getProductionYear(),
                    'productionCountry' => $this->getProductionCountrys(),
                    'actors' => $this->getActors(),
                    'regisseurs' => $this->getRegisseurs(),
                    'genres' => $this->getGenres(),
                    'xrelRatings' => $this->getRatings()
                );
                break;
            case 'game':
                switch ($this->subSectionVar)
                {
                    case 'PC':
                        $this->searchResult = array(
                            'title' => $this->getTitle(),
                            'coverLink' => $this->getCoverLink(),
                            'genres' => $this->getGenres(),
                            'usk' => $this->getUSK(),
                            'developer' => $this->getDeveloper(),
                            'publisher' => $this->getPublisher(),
                            'description' => $this->getDescription(),
                            'xrelReleasesLink' => $this->getReleasesLink(),
                            'xrelRatings' => $this->getRatings(),
                            'plattform' => $this->getPlattform()
                        );
                        break;
                    case 'Konsole':
                        $this->searchResult = array(
                            'title' => $this->getTitle(),
                            'coverLink' => $this->getCoverLink(),
                            'genres' => $this->getGenres(),
                            'usk' => $this->getUSK(),
                            'developer' => $this->getDeveloper(),
                            'publisher' => $this->getPublisher(),
                            'description' => $this->getDescription(),
                            'xrelReleasesLink' => $this->getReleasesLink(),
                            'xrelRatings' => $this->getRatings(),
                            'plattform' => $this->getPlattform()
                        );
                        break;
                }
                break;
        }

        if ($this->getReleases == true)
        {
            $this->searchResult['releases'] = $this->getAllReleases();
        }

        switch ($this->outputType)
        {
            case 'raw':
                return $this->searchResult;
                break;
            case 'json':
                return json_encode($this->searchResult);
                break;
            default:
                return $this->searchResult;
                break;
        }

    }

    protected function getReleasesLink()
    {
        $url = 'http://www.xrel.to' . $this->resultURL;
        $url = str_ireplace('.html', '/releases.html', $url);

        $this->releasesLink = $url;

        return $url;
    }

    protected function getDescription()
    {
        switch ($this->sectionVar)
        {
            case 'movie':
                $descriptionPattern = '/<div class="article_text" style="margin\:0\;">(.*?)<\/div>/s';
                break;
            case 'game':
                switch ($this->subSectionVar)
                {
                    case 'PC':
                    case 'Konsole':
                        $descriptionPattern = '/<div class="article_text" style="margin\:0\;">(.*?)<\/div>/s';
                        break;
                }
                break;
        }
        preg_match_all($descriptionPattern, $this->detailHTML, $description);

        if (isset($description[1][0]))
        {
            $description = $description[1][0];
            preg_match_all('/<table class="bb_table">.*<\/table>/s', $description, $sieheAuch);

            if (isset($sieheAuch[0][0]))
            {
                $description = str_ireplace($sieheAuch[0][0], '', $description);
            }

            $description = preg_replace('#<a(.*)>(.*)</a>#Uis', '\\2', $description);
            $description = trim($description);
        }
        else
        {
            $description = '';
        }
        return $this->convertToUTF8(htmlspecialchars_decode($description));
    }

    protected function getTitle()
    {
        switch ($this->sectionVar)
        {
            case 'movie':
                $titlePattern = '/<h3>(.*?)<\/h3>/';
                break;
            case 'game':
                switch ($this->subSectionVar)
                {
                    case 'PC':
                    case 'Konsole':
                        $titlePattern = '/<h3>(.*?)<\/h3>/';
                        break;

                }
                break;
        }

        preg_match($titlePattern, $this->detailHTML, $title);
        return $this->convertToUTF8($title[1]);
    }

    protected function getOriginalTitle()
    {
        $originalTitlePattern = '/<span class="sub" title="Originaltitel">\((.*?)\)<\/span>/';

        preg_match_all($originalTitlePattern, $this->detailHTML, $originaltitle);
        if (isset($originaltitle[1][0]))
        {
            $originaltitle = $originaltitle[1][0];
            if ($originaltitle == $this->getTitle())
            {
                $originaltitle = '';
            }
        }
        else
        {
            $originaltitle = '';
        }
        return $this->convertToUTF8($originaltitle);
    }

    protected function getRuntime()
    {
        $runtimePattern = '/<div class="l_left">Laufzeit:<\/div> <div class="l_right" title="(([0-9]+?)) Min\."?>(.*?)<\/div>/';

        preg_match($runtimePattern, $this->detailHTML, $runtime);

        if (empty($runtime[1]))
        {
            $runtime = '';
        }
        else
        {
            $runtime = $runtime[1];
        }
        return $runtime;
    }

    protected function getUSK()
    {

        $uskPattern = '/<div class="l_left">Freigegeben ab \(Jahre\):<\/div> <div class="l_right">(.*?)<\/div>/';
        preg_match($uskPattern, $this->detailHTML, $usk);

        $usk = $usk[1];

        return $usk;
    }

    protected function getFSK()
    {
        $fskPattern = '/<div class="l_left">Freigegeben ab \(Jahre\):<\/div> <div class="l_right">(.*?)<\/div>/';

        preg_match($fskPattern, $this->detailHTML, $fsk);

        if (empty($fsk[1]) AND $fsk[1] != 0)
        {
            $fsk = '';
        }
        else
        {
            $fsk = $fsk[1];
        }
        return $fsk;
    }

    protected function getDeveloper()
    {
        switch ($this->sectionVar)
        {
            case 'game':
                switch ($this->subSectionVar)
                {
                    case 'PC':
                    case 'Konsole':
                        $developerPattern = '/<div class="l_left">Entwickler:<\/div> <div class="l_right">(.*?)<\/div>/';
                        break;
                }
                break;
        }

        if (preg_match($developerPattern, $this->detailHTML, $developer))
        {
            preg_match($developerPattern, $this->detailHTML, $developer);

            if (isset($developer[1]))
            {
                $developer = $developer[1];
            }
        }
        else
        {
            $developer = '';
        }
        return $this->convertToUTF8($developer);
    }

    protected function getPublisher()
    {
        $publisherPattern = '/<div class="l_left">Herausgeber:<\/div> <div class="l_right">(.*?)<\/div>/';

        preg_match_all($publisherPattern, $this->detailHTML, $publisher);
        if (isset($publisher[1][0]))
        {
            $publisher = $publisher[1][0];
        }
        return $this->convertToUTF8($publisher);
    }

    protected function getCinemaDE()
    {
        $kinostartDePattern = '/<div class="extinfo_box_date">(.*?)<div class="sub">.*?<\/div> <\/div> <ul>  <li>Kinostart \((.*?)\)<\/li>/';

        preg_match_all($kinostartDePattern, $this->detailHTML, $kinostart_de);

        if (isset($kinostart_de))
        {
            foreach ($kinostart_de[2] as $key => $value)
            {
                if ($value == 'DE')
                {
                    $kinostart = $kinostart_de[1][$key];
                    $kinostart = trim($kinostart);
                }
            }
            if (!isset($kinostart))
            {
                $kinostart = '';
            }
        }
        else
        {
            $kinostart = '';
        }
        return $kinostart;
    }

    protected function getCinemaUS()
    {
        $kinostartUsPattern = '/<div class="extinfo_box_date">(.*?)<div class="sub">.*?<\/div> <\/div> <ul>  <li>Kinostart \((.*?)\)<\/li>/';

        preg_match_all($kinostartUsPattern, $this->detailHTML, $kinostart_de);

        if (isset($kinostart_de))
        {
            foreach ($kinostart_de[2] as $key => $value)
            {
                if ($value == 'U.S.')
                {
                    $kinostart = $kinostart_de[1][$key];
                    $kinostart = trim($kinostart);
                }
            }
            if (!isset($kinostart))
            {
                $kinostart = '';
            }
        }
        else
        {
            $kinostart = '';
        }
        return $kinostart;
    }

    protected function getImdbLink()
    {
        $imdbPattern = '/http:\/\/[a-z]*\.imdb\.com\/title\/([A-Za-z0-9]*)\//';

        preg_match_all($imdbPattern, $this->detailHTML, $imdb);

        if (empty($imdb[0]))
        {
            $imdb = '';
        }
        else
        {
            $imdb = $imdb[0][0];
        }
        return $imdb;
    }

    protected function getProductionCountrys()
    {
        $productionCountryPattern = '/<div class="l_left">Produktion:<\/div> <div class="l_right">(.*?) ([0-9]*?)<\/div>/';

        preg_match($productionCountryPattern, $this->detailHTML, $production);

        if (isset($production[1]))
        {
            $productionCountry = $production[1];
        }
        else
        {
            $productionCountry = '';
        }

        $productionCountryArray = array();

        if ('' != $productionCountry)
        {
            if (strstr($productionCountry, '/'))
            {
                $productionCountryArray = explode('/', $productionCountry);
            }
            else
            {
                array_push($productionCountryArray, $productionCountry);
            }
        }
        return $this->convertToUTF8($productionCountryArray);
    }

    protected function getProductionYear()
    {
        $productionYearPattern = '/<div class="l_left">Produktion:<\/div> <div class="l_right">(.*?) ([0-9]*?)<\/div>/';

        preg_match($productionYearPattern, $this->detailHTML, $production);

        if (isset($production[2]))
        {
            $productionYear = $production[2];
        }
        else
        {
            $productionYear = '';
        }
        return $productionYear;
    }

    protected function getCoverLink()
    {
        switch ($this->sectionVar)
        {
            case 'movie':
                $coverLinkPattern = '/<div id="poster" style="line-height:0;"><div>  <img src="(.*?)" alt="" class="reflect rheight20 ropacity33" \/>/';
                break;
            case 'game':
                switch ($this->subSectionVar)
                {
                    case 'PC':
                    case 'Konsole':
                        $coverLinkPattern = '/<div id="poster" style="line-height:0;"><div>  <img src="(.*?)" alt="" class="reflect rheight20 ropacity33" \/>/';
                        break;
                }
        }

        preg_match_all($coverLinkPattern, $this->detailHTML, $coverLink);

        if (isset($coverLink[1][0]))
        {
            $coverLink = 'http://www.xrel.to' . $coverLink[1][0];
        }
        else
        {
            $coverLink = '';
        }
        return $coverLink;
    }

    protected function getRatings()
    {
        switch ($this->sectionVar)
        {
            case 'movie':
                $toFindForPregMatchPattern = '/Bisher stimmte niemand ab/';
                break;
            case 'game':
                switch ($this->subSectionVar)
                {
                    case 'PC':
                        $toFindForPregMatchPattern = '/Bewertung nicht verfügbar\./';
                        break;
                    case 'Konsole':
                        $toFindForPregMatchPattern = '/Bisher stimmte niemand ab/';
                        break;
                }
                break;
        }

        if (!preg_match($toFindForPregMatchPattern, $this->detailHTML))
        {
            $ratingPattern = '/Bewertung\: <span id="star_rating_avg">(.*?)<\/span> \/ (.*?) \(<span id="star_rating_votes">(.*?)<\/span>\)/';

            preg_match_all($ratingPattern, $this->detailHTML, $rating);

            if(isset($rating[3][0]))
            {
                $voices = str_replace('Stimmen', '', $rating[3][0]);
                $voices = str_replace('Stimme', '', $voices);
                $voices = trim($voices);

                $ratings['averangeRating'] = str_replace(',', '.', $rating[1][0]);
                $ratings['scale'] = str_replace(',', '.', $rating[2][0]);
                $ratings['voices'] = $voices;
            }
            else
            {
                $ratings = array();
            }

        }
        else
        {
            $ratings = array();
        }

        return $ratings;
    }

    protected function getActors()
    {
        if (preg_match('/Regisseur:/', $this->detailHTML))
        {
            $tempPattern = '/Schauspieler\: <\/div> <div class="horiz_line_dotted"><\/div>(.*?)<div class="horiz_line_dotted"><\/div>/';
        }
        else
        {
            $tempPattern = '/Schauspieler\: <\/div> <div class="horiz_line_dotted"><\/div>(.*?)<br \/>  <\/div>/';

        }

        preg_match_all($tempPattern, $this->detailHTML, $tempActors);

        if (isset($tempActors[1][0]))
        {
            $tempActors = $tempActors[1][0];

            $peoplePattern = '/<a( class="camouflagedlink")? href="\/person\/[^>]*">(.*?)<\/a>/';

            preg_match_all($peoplePattern, $tempActors, $people);

            if (isset($people[2]))
            {
                $actors = array_unique($people[2]);
            }
            else
            {
                $actors = array();
            }
        }
        else
        {
            $actors = array();
        }

        return $this->convertToUTF8(array_unique($actors));
    }

    protected function getRegisseurs()
    {
        $tempPattern = '/Regisseur\: <\/div> <div class="horiz_line_dotted"><\/div>(.*?)<div class="clear"><\/div>/';

        preg_match_all($tempPattern, $this->detailHTML, $tempRegisseurs);

        if (isset($tempRegisseurs[1][0]))
        {
            $tempRegisseurs = $tempRegisseurs[1][0];

            $peoplePattern = '/<a( class="camouflagedlink")? href="\/person\/[^>]*">(.*?)<\/a>/';

            preg_match_all($peoplePattern, $tempRegisseurs, $people);

            if (isset($people[2]))
            {
                $regisseurs = array_unique($people[2]);
            }
            else
            {
                $regisseurs = array();
            }
        }
        else
        {
            $regisseurs = array();
        }
        return $this->convertToUTF8(array_unique($regisseurs));
    }

    protected function getGenres()
    {
        switch ($this->sectionVar)
        {
            case 'movie':
                $pattern = '/<div class="l_left">.*?<\/div> <div class="l_right"( title="([0-9]+?) Min\.")?>(.*?)<\/div>/';
                $toFindForPregMatchPattern = '/<div class="l_left">Genre:<\/div>/';
                break;
            case 'game':
                switch ($this->subSectionVar)
                {
                    case 'PC':
                    case 'Konsole':
                        $pattern = '/<div class="l_left">.*?<\/div> <div class="l_right"( title="([0-9]+?) Min\.")?>(.*?)<\/div>/';
                        $toFindForPregMatchPattern = '/<div class="l_left">Genre:<\/div>/';
                        break;
                }
                break;
        }

        if (preg_match($toFindForPregMatchPattern, $this->detailHTML))
        {
            preg_match_all($pattern, $this->detailHTML, $genreDetails);

            if (isset($genreDetails[3][0]))
            {
                $genre = $genreDetails[3][0];

                if (strstr($genre, '/'))
                {
                    $genre = explode('/', $genre);
                }
                elseif (strstr($genre, ','))
                {
                    $genre = explode(',', $genre);
                }
                else
                {
                    $genre = array($genre);
                }

                $tt = 0;
                $newGenre = array();

                foreach ($genre as $key => $value)
                {
                    if (!strstr($value, 'User'))
                    {
                        $newGenre[$tt] = $value;
                        $tt++;
                    }
                }
            }
            else
            {
                $newGenre = array();
            }
        }
        else
        {
            $newGenre = array();
        }
        return $this->convertToUTF8(array_unique($newGenre));

    }

    public function getResults()
    {
        if (empty($this->resultList))
        {
            throw new Exception('Array resultList is empty' . __METHOD__);
        }
        $count = 0;

        foreach ($this->resultList as $resultURL)
        {
            $this->searchResults[] = $this->getResult($resultURL);

            if (++$count >= $this->limit)
            {
                break;
            }
        }

        switch ($this->outputType)
        {
            case 'raw':
                return $this->searchResults;
                break;
            case 'json':
                return json_encode($this->searchResults);
                break;
            default:
                return $this->searchResults;
                break;
        }
    }

    protected function getReleasesDetailHTML()
    {
        $this->releasesDetailHTML = self::httpGET($this->releasesLink, $this->cookieValues);
    }

    public function getReleases($option = true)
    {
        if ($option == true)
        {
            $this->getReleases = true;
        }
        else
        {
            $this->getReleases = false;
        }
    }

    protected function getAllReleases()
    {
        $this->getReleasesDetailHTML();
        $this->setReleasesCount();

        for ($i = 0; $i <= $this->releasesCount; $i++)
        {
            $this->tempCount = $i;

            switch ($this->sectionVar)
            {
                case 'movie':
                    $this->releaseResults[] = array(
                        'releaseTitle' => $this->getReleaseTitle(),
                        'releaseGroup' => $this->getReleaseGroup(),
                        'releaseDate' => $this->getReleaseDate(),
                        'releaseTime' => $this->getReleaseTime(),
                        'releaseAudio' => $this->getReleaseAudio(),
                        'releaseVideoSource' => $this->getReleaseVideoSource(),
                        'releaseGenre' => $this->getReleaseGenre(),
                        'releaseLanguage' => $this->getReleaseLanguage(),
                        'releaseDetailLink' => $this->getReleaseDetailLink(),
                        'releaseRatings' => $this->getReleaseRatings(),
                        'xrelReleaseID' => $this->getXrelReleaseID(),
                        'releaseIsNuked' => $this->getReleaseNuked(),
                        'releaseIsProperOrRepack' => $this->getReleaseProperOrRepack()
                    );
                    break;
                case 'game':
                    switch ($this->subSectionVar)
                    {
                        case 'PC':
                            $this->releaseResults[] = array(
                                'releaseTitle' => $this->getReleaseTitle(),
                                'releaseGroup' => $this->getReleaseGroup(),
                                'releaseDate' => $this->getReleaseDate(),
                                'releaseTime' => $this->getReleaseTime(),
                                'xrelReleaseID' => $this->getXrelReleaseID(),
                                'releaseLanguage' => $this->getReleaseLanguage(),
                                'releaseDetailLink' => $this->getReleaseDetailLink(),
                                'releaseRatings' => $this->getReleaseRatings(),
                                'releaseIsNuked' => $this->getReleaseNuked(),
                                'releaseIsProperOrRepack' => $this->getReleaseProperOrRepack(),
                                'releaseGenre' => $this->getReleaseGenre()
                            );
                            break;
                        case 'Konsole':
                            $this->releaseResults[] = array(
                                'releaseTitle' => $this->getReleaseTitle(),
                                'releaseGroup' => $this->getReleaseGroup(),
                                'releaseDate' => $this->getReleaseDate(),
                                'releaseTime' => $this->getReleaseTime(),
                                'xrelReleaseID' => $this->getXrelReleaseID(),
                                'releaseLanguage' => $this->getReleaseLanguage(),
                                'releaseDetailLink' => $this->getReleaseDetailLink(),
                                'releaseRatings' => $this->getReleaseRatings(),
                                'releaseIsNuked' => $this->getReleaseNuked(),
                                'releaseIsProperOrRepack' => $this->getReleaseProperOrRepack(),
                                'releaseGenre' => $this->getReleaseGenre(),
                                'releasePlattform' => $this->getReleasePlattform()
                            );
                            break;
                    }
                    break;
            }
            if (($this->releasesCount - 1) == $this->tempCount)
            {
                break;
            }

        }
        return $this->releaseResults;

    }

    public function setReleasesCount()
    {
        switch ($this->sectionVar)
        {
            case 'movie':
                $releaseTitlePattern = '/<a href="\/movie-nfo\/[0-9]*\/(.*?).html" class="sub_link">/';
                break;
            case 'game':
                switch ($this->subSectionVar)
                {
                    case 'PC':
                        $releaseTitlePattern = '/<a href="\/game-nfo\/[0-9]*\/(.*?).html" class="sub_link">/';
                        break;
                    case 'Konsole':
                        $releaseTitlePattern = '/<a href="\/console-nfo\/[0-9]*\/(.*?).html" class="sub_link">/';
                        break;
                }
                break;
        }

        preg_match_all($releaseTitlePattern, $this->releasesDetailHTML, $releaseTitle);

        $this->releasesCount = count($releaseTitle[1]);

        if ($this->releasesCount > $this->getReleasesLimit && null != $this->getReleasesLimit)
        {
            $this->releasesCount = $this->getReleasesLimit;
        }

    }

    protected function getReleaseGroup()
    {
        $releaseGroupPattern = '/<div class="release_grp">\s*<a href="\/group-([a-zA-z0-9]*?)-release-list.html"/';

        preg_match_all($releaseGroupPattern, $this->releasesDetailHTML, $releaseGroup);

        $releaseGroup = $releaseGroup[1][$this->tempCount];

        return $releaseGroup;
    }

    protected function getReleaseTitle()
    {
        switch ($this->sectionVar)
        {
            case 'movie':
                $releaseTitlePattern = '/<a href="\/movie-nfo\/[0-9]*\/(.*?).html" class="sub_link">/';
                break;
            case 'game':
                switch ($this->subSectionVar)
                {
                    case 'PC':
                        $releaseTitlePattern = '/<a href="\/game-nfo\/[0-9]*\/(.*?).html" class="sub_link">/';
                        break;
                    case 'Konsole':
                        $releaseTitlePattern = '/<a href="\/console-nfo\/[0-9]*\/(.*?).html" class="sub_link">/';
                        break;
                }
                break;
        }

        preg_match_all($releaseTitlePattern, $this->releasesDetailHTML, $releaseTitle);

        $newTitle = str_ireplace("-", ".", $releaseTitle[1][$this->tempCount]);



        $releaseGroup = $this->getReleaseGroup();

        $newTitle = str_replace('.' . $releaseGroup, '-' . $releaseGroup, $newTitle);

        return $newTitle;
    }

    protected function getReleaseDate()
    {
        $releaseDatePattern = '/<div class="release_date">(.*?)<br\s*\/>/';
        preg_match_all($releaseDatePattern, $this->releasesDetailHTML, $releaseDate);
        return $releaseDate[1][$this->tempCount];
    }

    protected function getReleaseTime()
    {
        $releaseTimePattern = '/<span class="sub">([0-9]*?):([0-9]*?) Uhr/';
        preg_match_all($releaseTimePattern, $this->releasesDetailHTML, $releaseTime);
        $releaseTimeArray = array();
        $releaseTimeArray['hour'] = $releaseTime[1][$this->tempCount];
        $releaseTimeArray['minute'] = $releaseTime[2][$this->tempCount];
        $releaseTimeArray['time'] = $releaseTime[1][$this->tempCount] . ':' . $releaseTime[2][$this->tempCount];

        return $releaseTimeArray;
    }

    protected function getReleaseAudio()
    {
        $releaseAudioPattern = '/<div class="release_type">\s*[a-zA-Z0-9\-\.]*<br\s*\/>\s*<span class="sub">(.*?)<\/span>/';
        preg_match_all($releaseAudioPattern, $this->releasesDetailHTML, $releaseAudio);
        return $releaseAudio[1][$this->tempCount];
    }

    protected function getReleaseVideoSource()
    {
        $releaseVideoSourcePattern = '/<div class="release_type">\s*(.*?)<br \/>\s*<span class="sub">\s*[a-zA-Z0-9\-]*<\/span>/';
        preg_match_all($releaseVideoSourcePattern, $this->releasesDetailHTML, $releaseVideoSource);
        return $releaseVideoSource[1][$this->tempCount];
    }

    protected function getReleaseGenre()
    {
        $releaseGenrePattern = '/class="sub_link"><span>(.*?)<\/span><\/a>\s*<\/div>\s*<div class="release_date">/';
        preg_match_all($releaseGenrePattern, $this->releasesDetailHTML, $releaseGenre);
        return $releaseGenre[1][$this->tempCount];
    }

    protected function getReleaseLanguage()
    {
        $releaseTitle = $this->getReleaseTitle();

        if (preg_match("/\.German\./i", $releaseTitle))
        {
            $releaseLanguage = 'DE';
        }
        elseif (preg_match("/\.MULTi\./i", $releaseTitle))
        {
            $releaseLanguage = 'MULTI';
        }
        else
        {
            $releaseLanguage = 'EN';
        }
        return $releaseLanguage;
    }

    protected function getReleaseDetailLink()
    {
        switch ($this->sectionVar)
        {
            case 'movie':
                $releaseDetailLink = 'http://www.xrel.to/movie-nfo/' . $this->getXrelReleaseID() . '/' . str_replace('.', '-', $this->getReleaseTitle()) . '.html';
                break;
            case 'game':
                switch ($this->subSectionVar)
                {
                    case 'PC':
                        $releaseDetailLink = 'http://www.xrel.to/game-nfo/' . $this->getXrelReleaseID() . '/' . str_replace('.', '-', $this->getReleaseTitle()) . '.html';
                        break;
                    case 'Konsole':
                        $releaseDetailLink = 'http://www.xrel.to/console-nfo/' . $this->getXrelReleaseID() . '/' . str_replace('.', '-', $this->getReleaseTitle()) . '.html';
                        break;
                    default:
                        throw new Exception('Unknown subSectionVar in ' . __METHOD__);

                }
                break;
            default:
                throw new Exception('Unknown sectionVar in ' . __METHOD__);
        }

        $this->setReleaseDetailLink($releaseDetailLink);

        return $releaseDetailLink;
    }

    protected function setReleaseDetailLink($releaseDetailLink)
    {
        if ('' == $releaseDetailLink || null == $releaseDetailLink)
        {
            throw new Exception('releaseDetailLink epmty or unknown in ' . __METHOD__);
        }
        $this->releaseDetailLink = $releaseDetailLink;
    }

    protected function getXrelReleaseID()
    {
        $releaseTitle = str_replace('.', '-', $this->getReleaseTitle());

        switch ($this->sectionVar)
        {
            case 'movie':
                $xrelReleaseIdPattern = '/<a href="\/movie-nfo\/([0-9]*)\/' . $releaseTitle . '.html" class="sub_link">/';
                break;
            case 'game':
                switch ($this->subSectionVar)
                {
                    case 'PC':
                        $xrelReleaseIdPattern = '/<a href="\/game-nfo\/([0-9]*)\/' . $releaseTitle . '.html" class="sub_link">/';
                        break;
                    case 'Konsole':
                        $xrelReleaseIdPattern = '/<a href="\/console-nfo\/([0-9]*)\/' . $releaseTitle . '.html" class="sub_link">/';
                        break;
                }
                break;
        }

        preg_match_all($xrelReleaseIdPattern, $this->releasesDetailHTML, $xrelReleaseID);
        return $xrelReleaseID[1][0];
    }

    protected function getReleaseDetailHTML()
    {
        $releaseDetailHTML = self::httpGET($this->releaseDetailLink, $this->cookieValues);
        if ('' == $releaseDetailHTML || null == $releaseDetailHTML)
        {
            throw new Exception('releaseDetailHTML-Code empty in ' . __METHOD__);
        }
        $this->releaseDetailHTML = $releaseDetailHTML;
    }

    protected function getReleaseRatings()
    {
        $this->getReleaseDetailHTML();

        if (preg_match('/Durchschn\. Releasebewertung/', $this->releaseDetailHTML))
        {
            $ratingPattern = '/Durchschn\. Releasebewertung<br \/> <span class="sub">Gesamt: ([^<]*)<\/span> <div class="horiz_line_dotted" style="margin-top:5px;"><\/div> <span class="sub">Bildqualit�t: <span class="headline1 rating_10">([^<]*)<\/span> \/ ([^<]*)<br \/> Tonqualit�t: <span class="headline1 rating_10">([^<]*)<\/span> \/ ([^<]*)<\/span>/sm';

            preg_match_all($ratingPattern, $this->releaseDetailHTML, $releaseRating);

            if (isset($releaseRating[1][0]))
            {
                $voices = str_replace('Stimmen', '', $releaseRating[1][0]);
                $voices = str_replace('Stimme', '', $voices);
                $voices = trim($voices);

                $releaseRatings['averangeAudioRating'] = str_replace(',', '.', $releaseRating[4][0]);
                $releaseRatings['audioScale'] = str_replace(',', '.', $releaseRating[5][0]);
                $releaseRatings['averangeVideoRating'] = str_replace(',', '.', $releaseRating[2][0]);
                $releaseRatings['videoScale'] = str_replace(',', '.', $releaseRating[3][0]);

                $releaseRatings['voices'] = $voices;
            }
            else
            {
                $releaseRatings = array();
            }

        }
        else
        {
            $releaseRatings = array();
        }

        return $releaseRatings;
    }

    protected function getReleaseNuked()
    {

        $releaseNukedPattern = '/Nuked: <\/div> <div class="l_right"> <img src="[^"]*" alt="(.*?)" \/>/';

        preg_match_all($releaseNukedPattern, $this->releaseDetailHTML, $releaseNuked);

        if(isset($releaseNuked[1][0]))
        {
            $status = $releaseNuked[1][0];
        }
        else
        {
            $status = 0;
        }

        switch ($status)
        {
            case 'Nein':
                $nukedStatus = 0;
                break;
            case 'Ja':
                $nukedStatus = 1;
                break;
            default:
                $nukedStatus = 0;
                break;
        }
        return $nukedStatus;
    }

    protected function getReleaseProperOrRepack()
    {

        $releaseProperOrRepackPattern = '/Repack oder Proper: <\/div> <div class="l_right">  <img src="[^"]*" alt="(.*?)" \/>/';

        preg_match_all($releaseProperOrRepackPattern, $this->releaseDetailHTML, $releaseProperOrRepack);

        switch ($releaseProperOrRepack[1][0])
        {
            case 'Nein':
                $properOrRepackStatus = 0;
                break;
            case 'Ja':
                $properOrRepackStatus = 1;
                break;
            default:
                $properOrRepackStatus = 0;
                break;
        }

        return $properOrRepackStatus;
    }

    protected function getPlattform()
    {
        if ($this->sectionVar == 'game')
        {
            return $this->subSectionVar;
        }
    }

    protected static function httpGET($requestURI, $cookieValues)
    {
        $ch = curl_init($requestURI);

        curl_setopt_array($ch, array(
                                    CURLOPT_RETURNTRANSFER => true,
                                    CURLOPT_FOLLOWLOCATION => false,
                                    CURLOPT_MAXREDIRS => 5,
                                    CURLOPT_COOKIE => $cookieValues,
                                    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 6.1; de; rv:1.9.1.7) Gecko/20091221 Firefox/3.5.7' // Firefox 3.5 on Windows 7
                               ));

        $r = 0;
        $html = self::curl_redirect_exec($ch, $r);
        curl_close($ch);

        return $html;
    }

    protected function curl_redirect_exec($ch, &$redirects, $curlopt_header = false)
    {
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($http_code == 301 || $http_code == 302)
        {
            list($header) = explode("\r\n\r\n", $data, 2);

            $matches = array();
            $url  = self::getRedirectUrl($header);

            $url_parsed = parse_url($url);

            if (isset($url_parsed))
            {
                curl_setopt($ch, CURLOPT_URL, $url);
                $redirects++;
                return self::curl_redirect_exec($ch, $redirects);
            }
        }

        if ($curlopt_header)
        {
            return $data;
        }
        else
        {
            list($tmp, $body) = explode("\r\n\r\n", $data, 2);
            return $body;
        }
    }

    protected static function getRedirectUrl($string)
    {
          $re1='.*?';	# Non-greedy match on filler
          $re2='(Location)';	# Word 1
          $re3='(:)';	# Any Single Character 1
          $re4='( )';	# White Space 1
          $re5='((?:http|https)(?::\\/{2}[\\w]+)(?:[\\/|\\.]?)(?:[^\\s"]*))';	# HTTP URL 1

          if ($c=preg_match_all ("/".$re1.$re2.$re3.$re4.$re5."/is", $string, $matches))
          {
              $word1=$matches[1][0];
              $c1=$matches[2][0];
              $ws1=$matches[3][0];
              $httpurl1=$matches[4][0];
              return $httpurl1;
          }
    }

    protected function convertToUTF8($toCheck)
    {
        if (is_array($toCheck))
        {
            return $this->convertArrayToUTF8($toCheck);
        }
        else
        {
            return $this->convertValueToUTF8($toCheck);
        }
    }

    protected function convertValueToUTF8($value)
    {
        return utf8_decode($value);
    }

    protected function convertArrayToUTF8($array)
    {
        $newArray = array();

        foreach ($array as $value)
        {
            $newArray[] = $this->convertValueToUTF8($value);
        }
        return $newArray;
    }

    public function changeOutputType($type)
    {
        switch ($type)
        {
            case 'raw':
                $this->outputType = 'raw';
                break;
            case 'json':
                $this->outputType = 'json';
                break;
            default;
                $this->outputType = 'raw';
                break;
        }
    }

    protected function getReleasePlattform()
    {
        $releaseTitle = $this->getReleaseTitle();

        if (preg_match('/[\.\-\_]wii[\.\-\_]/is', $releaseTitle))
        {
            $releasePlattform['short'] = 'wii';
            $releasePlattform['long'] = 'Nintendo Wii';
        }
        elseif (preg_match('/[\.\-\_]ps3[\.\-\_]/is', $releaseTitle))
        {
            $releasePlattform['short'] = 'ps3';
            $releasePlattform['long'] = 'PlayStation 3';
        }
        elseif (preg_match('/[\.\-\_]xbox360[\.\-\_]/is', $releaseTitle))
        {
            $releasePlattform['short'] = 'xbox360';
            $releasePlattform['long'] = 'Xbox 360';
        }
        elseif (preg_match('/[\.\-\_]psp[\.\-\_]/is', $releaseTitle))
        {
            $releasePlattform['short'] = 'psp';
            $releasePlattform['long'] = 'Playstation Portable';
        }
        elseif (preg_match('/[\.\-\_]nds[\.\-\_]/is', $releaseTitle))
        {
            $releasePlattform['short'] = 'nds';
            $releasePlattform['long'] = 'Nintendo DS';
        }
        else
        {
            $releasePlattform = array();
        }
        return $releasePlattform;
    }

}

?>

