<?php
namespace SJBR\StaticInfoTables\Controller;

/*
 *  Copyright notice
 *
 *  (c) 2013-2021 Stanislas Rolland <typo3AAAA(arobas)sjbr.ca>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 */

use SJBR\StaticInfoTables\Domain\Model\Country;
use SJBR\StaticInfoTables\Domain\Model\CountryZone;
use SJBR\StaticInfoTables\Domain\Model\Language;
use SJBR\StaticInfoTables\Domain\Model\LanguagePack;
use SJBR\StaticInfoTables\Domain\Repository\CountryRepository;
use SJBR\StaticInfoTables\Domain\Repository\CountryZoneRepository;
use SJBR\StaticInfoTables\Domain\Repository\CurrencyRepository;
use SJBR\StaticInfoTables\Domain\Repository\LanguageRepository;
use SJBR\StaticInfoTables\Domain\Repository\LanguagePackRepository;
use SJBR\StaticInfoTables\Domain\Repository\TerritoryRepository;
use SJBR\StaticInfoTables\Utility\LocaleUtility;
use TYPO3\CMS\Core\Localization\Locales;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Static Info Tables Manager controller
 */
class ManagerController extends ActionController
{
    /**
     * @var string Name of the extension this controller belongs to
     */
    protected $extensionName = 'StaticInfoTables';

    /**
     * @var CountryRepository
     */
    protected $countryRepository;

    /**
     * Dependency injection of the Country Repository
     *
     * @param CountryRepository $countryRepository
     * @return void
     */
    public function injectCountryRepository(CountryRepository $countryRepository)
    {
        $this->countryRepository = $countryRepository;
    }

    /**
     * @var CountryZoneRepository
     */
    protected $countryZoneRepository;

    /**
     * Dependency injection of the Country Zone Repository
     *
     * @param CountryZoneRepository $countryZoneRepository
     * @return void
     */
    public function injectCountryZoneRepository(CountryZoneRepository $countryZoneRepository)
    {
        $this->countryZoneRepository = $countryZoneRepository;
    }

    /**
     * @var CurrencyRepository
     */
    protected $currencyRepository;

    /**
     * Dependency injection of the Currency Repository
     *
     * @param CurrencyRepository $currencyRepository
     * @return void
     */
    public function injectCurrencyRepository(CurrencyRepository $currencyRepository)
    {
        $this->currencyRepository = $currencyRepository;
    }

    /**
     * @var LanguageRepository
     */
    protected $languageRepository;

    /**
     * Dependency injection of the Language Repository
     *
     * @param LanguageRepository $languageRepository
     * @return void
     */
    public function injectLanguageRepository(LanguageRepository $languageRepository)
    {
        $this->languageRepository = $languageRepository;
    }

    /**
     * @var TerritoryRepository
     */
    protected $territoryRepository;

    /**
     * Dependency injection of the Territory Repository
     *
     * @param TerritoryRepository $territoryRepository
     * @return void
     */
    public function injectTerritoryRepository(TerritoryRepository $territoryRepository)
    {
        $this->territoryRepository = $territoryRepository;
    }

    /**
     * Display general information
     */
    public function informationAction()
    {
        $this->view->assign(
            'actions',
            [
                [
                    'code' => 'newLanguagePack',
                    'title' => 'createLanguagePackTitle',
                    'description' => 'createLanguagePackDescription',
                ],
                [
                    'code' => 'testForm',
                    'title' => 'testFormTitle',
                    'description' => 'testFormDescription',
                ],
                [
                    'code' => 'sqlDumpNonLocalizedData',
                    'title' => 'sqlDumpNonLocalizedDataTitle',
                    'description' => 'sqlDumpNonLocalizedDataDescription',
                ],
            ]
        );
    }

    /**
     * Display the language pack creation form
     *
     * @param LanguagePack $languagePack
     */
    public function newLanguagePackAction(LanguagePack $languagePack = null)
    {
        if (!is_object($languagePack)) {
            $languagePack = new LanguagePack();
        }
        $languagePack->setVersion($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS'][GeneralUtility::camelCaseToLowerCaseUnderscored($this->extensionName)]['version']);
        $languagePack->setAuthor($GLOBALS['BE_USER']->user['realName']);
        $languagePack->setAuthorEmail($GLOBALS['BE_USER']->user['email']);
        $localeUtility = GeneralUtility::makeInstance(LocaleUtility::class);
        $this->view->assign('locales', $localeUtility->getLocales());
        $this->view->assign('languagePack', $languagePack);
    }

    /**
     * Creation/update a language pack for the Static Info Tables
     *
     * @param LanguagePack $languagePack
     */
    public function createLanguagePackAction(LanguagePack $languagePack)
    {
        // Add the localization columns
        $locale = $languagePack->getLocale();
        // Get the English name of the locale
        $localeUtility = new LocaleUtility();
        $language = $localeUtility->getLanguageFromLocale($locale);
        $languagePack->setLanguage($language);
        $languagePack->setTypo3VersionRange($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS'][GeneralUtility::camelCaseToLowerCaseUnderscored($this->extensionName)]['constraints']['depends']['typo3']);
        // If version is not set, use the version of the base extension
        if (!$languagePack->getVersion()) {
            $languagePack->setVersion($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS'][GeneralUtility::camelCaseToLowerCaseUnderscored($this->extensionName)]['version']);
        }
        $this->countryRepository->addLocalizationColumns($locale);
        $this->countryZoneRepository->addLocalizationColumns($locale);
        $this->currencyRepository->addLocalizationColumns($locale);
        $this->languageRepository->addLocalizationColumns($locale);
        $this->territoryRepository->addLocalizationColumns($locale);
        // Store the Language Pack
        $languagePackRepository = GeneralUtility::makeInstance(LanguagePackRepository::class);
        $messages = $languagePackRepository->writeLanguagePack($languagePack);
        if (count($messages)) {
            foreach ($messages as $message) {
                $this->addFlashMessage($message, '', AbstractMessage::OK);
            }
        }
        $this->forward('information');
    }

    /**
     * Display a test form
     *
     * @param Country $country
     * @param CountryZone $countryZone
     * @param Language $language
     */
    public function testFormAction(Country $country = null, CountryZone $countryZone = null, Language $language = null)
    {
        if (is_object($country) && (is_object($countryZone) || !$country->getCountryZones()->count())) {
            $this->forward('testFormResult', 'Manager', $this->extensionName, ['country' => $country, 'countryZone' => $countryZone, 'language' => $language]);
        }
        if (is_object($country)) {
            $this->view->assign('selectedCountry', $country);
        }
        if (is_object($countryZone)) {
            $this->view->assign('selectedCountryZone', $countryZone);
        }
        if (is_object($language)) {
            $this->view->assign('selectedLanguage', $language);
        }
    }

    /**
     * Display the test form result
     *
     * @param Country $country
     * @param CountryZone $countryZone
     * @param Language $language
     */
    public function testFormResultAction(Country $country = null, CountryZone $countryZone = null, Language $language = null)
    {
        $this->view->assign('country', $country);
        $currencies = $this->currencyRepository->findByCountry($country);
        if ($currencies->count()) {
            $this->view->assign('currency', $currencies[0]);
        }
        if (is_object($countryZone)) {
            $this->view->assign('countryZone', $countryZone);
        }
        $this->view->assign('language', $language);
        $territories = $this->territoryRepository->findByCountry($country);
        if ($territories->count()) {
            $this->view->assign('territory', $territories[0]);
        }
    }

    /**
     * Creation/update a language pack for the Static Info Tables
     */
    public function sqlDumpNonLocalizedDataAction()
    {
        // Create a SQL dump of non-localized data
        $dumpContent = [];
        $dumpContent[] = $this->countryRepository->sqlDumpNonLocalizedData();
        $dumpContent[] = $this->countryZoneRepository->sqlDumpNonLocalizedData();
        $dumpContent[] = $this->currencyRepository->sqlDumpNonLocalizedData();
        $dumpContent[] = $this->languageRepository->sqlDumpNonLocalizedData();
        $dumpContent[] = $this->territoryRepository->sqlDumpNonLocalizedData();
        // Write the SQL dump file
        $extensionKey = GeneralUtility::camelCaseToLowerCaseUnderscored($this->extensionName);
        $extensionPath = ExtensionManagementUtility::extPath($extensionKey);
        $filename = 'export-ext_tables_static+adt.sql';
        GeneralUtility::writeFile($extensionPath . $filename, implode(LF, $dumpContent));
        $message = LocalizationUtility::translate('sqlDumpCreated', $this->extensionName) . ' ' . $extensionPath . $filename;
        $this->addFlashMessage($message, '', AbstractMessage::OK);
        $this->forward('information');
    }

    /**
     * Get the typo3-supported locale options for the language pack creation
     *
     * @return array An array of language objects
     */
    protected function getLocales()
    {
        $localeArray = [];
        $locales = GeneralUtility::makeInstance(Locales::class);
        $languages = $locales->getLanguages();
        foreach ($languages as $locale => $language) {
            // No language pack for English
            if ($locale != 'default') {
                $languageObject = new Language();
                $languageObject->setCollatingLocale($locale);
                $localizedLanguage = LocalizationUtility::translate('lang_' . $locale, 'Lang');
                $label = ($localizedLanguage ? $localizedLanguage : $language) . ' (' . $locale . ')';
                $languageObject->setNameEn($label);
                $localeArray[$label] = $languageObject;
            }
        }
        ksort($localeArray);
        return $localeArray;
    }

    /**
     * Get language name from locale
     *
     * @param string $locale
     * @return string Language name
     */
    protected function getLanguageFromLocale($locale)
    {
        $locales = GeneralUtility::makeInstance(Locales::class);
        $languages = $locales->getLanguages();
        $language = $languages[$locale];
        return $language . ' (' . $locale . ')';
    }
    
    protected function getErrorFlashMessage()
    {
    	return false;
    }
}