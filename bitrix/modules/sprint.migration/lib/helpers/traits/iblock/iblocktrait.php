<?php

namespace Sprint\Migration\Helpers\Traits\Iblock;

use CIBlock;
use Sprint\Migration\Exceptions\HelperException;
use Sprint\Migration\Helpers\UserGroupHelper;

trait IblockTrait
{
    /**
     * Получает инфоблок, бросает исключение если его не существует
     * @param $code string|array - код или фильтр
     * @param string $typeId
     * @throws HelperException
     * @return array|void
     */
    public function getIblockIfExists($code, $typeId = '')
    {
        $item = $this->getIblock($code, $typeId);
        if ($item && isset($item['ID'])) {
            return $item;
        }
        $this->throwException(__METHOD__, 'iblock not found');
    }

    /**
     * Получает id инфоблока, бросает исключение если его не существует
     * @param $code string|array - код или фильтр
     * @param string $typeId
     * @throws HelperException
     * @return int|void
     */
    public function getIblockIdIfExists($code, $typeId = '')
    {
        $item = $this->getIblock($code, $typeId);
        if ($item && isset($item['ID'])) {
            return $item['ID'];
        }

        $this->throwException(__METHOD__, 'iblock id not found');
    }

    /**
     * Получает инфоблок
     * @param $code int|string|array - id, код или фильтр
     * @param string $typeId
     * @return array|false
     */
    public function getIblock($code, $typeId = '')
    {
        if (is_array($code)) {
            $filter = $code;
        } elseif (is_numeric($code)) {
            $filter = ['ID' => $code];
        } else {
            $filter = ['=CODE' => $code];
        }

        if (!empty($typeId)) {
            $filter['=TYPE'] = $typeId;
        }

        $filter['CHECK_PERMISSIONS'] = 'N';

        /** @noinspection PhpDynamicAsStaticMethodCallInspection */

        $item = CIBlock::GetList(['SORT' => 'ASC'], $filter)->Fetch();
        return $this->prepareIblock($item);
    }

    /**
     * Получает список сайтов для инфоблока
     * @param $iblockId
     * @return array
     */
    public function getIblockSites($iblockId)
    {
        $dbres = CIBlock::GetSite($iblockId);
        return $this->fetchAll($dbres, false, 'LID');
    }

    /**
     * Получает id инфоблока
     * @param $code string|array - код или фильтр
     * @param string $typeId
     * @return int
     */
    public function getIblockId($code, $typeId = '')
    {
        $iblock = $this->getIblock($code, $typeId);
        return ($iblock && isset($iblock['ID'])) ? $iblock['ID'] : 0;
    }

    /**
     * Получает список инфоблоков
     * @param array $filter
     * @return array
     */
    public function getIblocks($filter = [])
    {
        $filter['CHECK_PERMISSIONS'] = 'N';

        /** @noinspection PhpDynamicAsStaticMethodCallInspection */
        $dbres = CIBlock::GetList(['SORT' => 'ASC'], $filter);
        $list = [];
        while ($item = $dbres->Fetch()) {
            $list[] = $this->prepareIblock($item);
        }
        return $list;
    }

    /**
     * Добавляет инфоблок если его не существует
     * @param array $fields , обязательные параметры - код, тип инфоблока, id сайта
     * @throws HelperException
     * @return int|void
     */
    public function addIblockIfNotExists($fields = [])
    {
        $this->checkRequiredKeys(__METHOD__, $fields, ['CODE', 'IBLOCK_TYPE_ID', 'LID']);

        $typeId = false;
        if (!empty($fields['IBLOCK_TYPE_ID'])) {
            $typeId = $fields['IBLOCK_TYPE_ID'];
        }

        $iblock = $this->getIblock($fields['CODE'], $typeId);
        if ($iblock) {
            return $iblock['ID'];
        }

        return $this->addIblock($fields);
    }

    /**
     * Добавляет инфоблок
     * @param $fields , обязательные параметры - код, тип инфоблока, id сайта
     * @throws HelperException
     * @return int|void
     */
    public function addIblock($fields)
    {
        $this->checkRequiredKeys(__METHOD__, $fields, ['CODE', 'IBLOCK_TYPE_ID', 'LID']);

        $default = [
            'ACTIVE' => 'Y',
            'NAME' => '',
            'CODE' => '',
            'LIST_PAGE_URL' => '',
            'DETAIL_PAGE_URL' => '',
            'SECTION_PAGE_URL' => '',
            'IBLOCK_TYPE_ID' => 'main',
            'LID' => ['s1'],
            'SORT' => 500,
            'GROUP_ID' => ['2' => 'R'],
            'VERSION' => 2,
            'BIZPROC' => 'N',
            'WORKFLOW' => 'N',
            'INDEX_ELEMENT' => 'N',
            'INDEX_SECTION' => 'N',
        ];

        $fields = array_replace_recursive($default, $fields);

        $ib = new CIBlock;
        $iblockId = $ib->Add($fields);

        if ($iblockId) {
            return $iblockId;
        }
        $this->throwException(__METHOD__, $ib->LAST_ERROR);
    }

    /**
     * Обновляет инфоблок
     * @param $iblockId
     * @param array $fields
     * @throws HelperException
     * @return int|void
     */
    public function updateIblock($iblockId, $fields = [])
    {
        $ib = new CIBlock;
        if ($ib->Update($iblockId, $fields)) {
            return $iblockId;
        }

        $this->throwException(__METHOD__, $ib->LAST_ERROR);

    }

    /**
     * Обновляет инфоблок если он существует
     * @param $code
     * @param array $fields
     * @throws HelperException
     * @return bool|int|void
     */
    public function updateIblockIfExists($code, $fields = [])
    {
        $iblock = $this->getIblock($code);
        if (!$iblock) {
            return false;
        }
        return $this->updateIblock($iblock['ID'], $fields);
    }

    /**
     * Удаляет инфоблок если он существует
     * @param $code
     * @param string $typeId
     * @throws HelperException
     * @return bool|void
     */
    public function deleteIblockIfExists($code, $typeId = '')
    {
        $iblock = $this->getIblock($code, $typeId);
        if (!$iblock) {
            return false;
        }
        return $this->deleteIblock($iblock['ID']);
    }

    /**
     * Удаляет инфоблок
     * @param $iblockId
     * @throws HelperException
     * @return bool|void
     */
    public function deleteIblock($iblockId)
    {
        if (CIBlock::Delete($iblockId)) {
            return true;
        }
        $this->throwException(__METHOD__, 'Could not delete iblock %s', $iblockId);
    }

    /**
     * Получает список полей инфоблока
     * @param $iblockId
     * @return array|bool
     */
    public function getIblockFields($iblockId)
    {
        return CIBlock::GetFields($iblockId);
    }

    /**
     * Сохраняет инфоблок
     * Создаст если не было, обновит если существует и отличается
     * @param array $fields , обязательные параметры - код, тип инфоблока, id сайта
     * @throws HelperException
     * @return bool|mixed
     */
    public function saveIblock($fields = [])
    {
        $this->checkRequiredKeys(__METHOD__, $fields, ['CODE', 'IBLOCK_TYPE_ID', 'LID']);

        $item = $this->getIblock($fields['CODE'], $fields['IBLOCK_TYPE_ID']);
        $exists = $this->prepareExportIblock($item);
        $fields = $this->prepareExportIblock($fields);

        if (empty($item)) {
            $ok = $this->getMode('test') ? true : $this->addIblock($fields);
            $this->outNoticeIf($ok, 'Инфоблок %s: добавлен', $fields['CODE']);
            return $ok;
        }

        if ($this->hasDiff($exists, $fields)) {
            $ok = $this->getMode('test') ? true : $this->updateIblock($item['ID'], $fields);
            $this->outNoticeIf($ok, 'Инфоблок %s: обновлен', $fields['CODE']);
            $this->outDiffIf($ok, $exists, $fields);
            return $ok;
        }

        $ok = $this->getMode('test') ? true : $item['ID'];
        if ($this->getMode('out_equal')) {
            $this->outIf($ok, 'Инфоблок %s: совпадает', $fields['CODE']);
        }
        return $ok;
    }

    /**
     * Сохраняет поля инфоблока
     * @param $iblockId
     * @param array $fields
     * @return bool
     */
    public function saveIblockFields($iblockId, $fields = [])
    {
        $exists = CIBlock::GetFields($iblockId);

        $exportExists = $this->prepareExportIblockFields($exists);
        $fields = $this->prepareExportIblockFields($fields);

        $fields = array_replace_recursive($exportExists, $fields);

        if (empty($exists)) {
            $ok = $this->getMode('test') ? true : $this->updateIblockFields($iblockId, $fields);
            $this->outNoticeIf($ok, 'Инфоблок %s: поля добавлены', $iblockId);
            return $ok;
        }

        if ($this->hasDiff($exportExists, $fields)) {
            $ok = $this->getMode('test') ? true : $this->updateIblockFields($iblockId, $fields);
            $this->outNoticeIf($ok, 'Инфоблок %s: поля обновлены', $iblockId);
            $this->outDiffIf($ok, $exportExists, $fields);
            return $ok;
        }

        if ($this->getMode('out_equal')) {
            $this->out('Инфоблок %s: поля совпадают', $iblockId);
        }

        return true;
    }

    /**
     * Получает инфоблок
     * Данные подготовлены для экспорта в миграцию или схему
     * @param $iblockId
     * @throws HelperException
     * @return array|void
     */
    public function exportIblock($iblockId)
    {
        $export = $this->prepareExportIblock(
            $this->getIblock($iblockId)
        );

        if (!empty($export['CODE'])) {
            return $export;
        }

        $this->throwException(__METHOD__, 'code not found');
    }

    /**
     * Получает список инфоблоков
     * Данные подготовлены для экспорта в миграцию или схему
     * @param array $filter
     * @return array
     */
    public function exportIblocks($filter = [])
    {
        $exports = [];
        $items = $this->getIblocks($filter);
        foreach ($items as $item) {
            if (!empty($item['CODE'])) {
                $exports[] = $this->prepareExportIblock($item);
            }
        }
        return $exports;
    }

    /**
     * Получает список полей инфоблока
     * Данные подготовлены для экспорта в миграцию или схему
     * @param $iblockId
     * @return array
     */
    public function exportIblockFields($iblockId)
    {
        return $this->prepareExportIblockFields(
            $this->getIblockFields($iblockId)
        );
    }

    /**
     * Обновляет поля инфоблока
     * @param $iblockId
     * @param $fields
     * @return bool
     */
    public function updateIblockFields($iblockId, $fields)
    {
        if ($iblockId && !empty($fields)) {
            CIBlock::SetFields($iblockId, $fields);
            return true;
        }
        return false;
    }

    /**
     * Получает права доступа к инфоблоку для групп
     * возвращает массив вида [$groupCode => $letter]
     *
     * @param $iblockId
     * @return array
     */
    public function getGroupPermissions($iblockId)
    {
        return CIBlock::GetGroupPermissions($iblockId);
    }

    /**
     * @param $iblockId
     * @throws HelperException
     * @return array
     */
    public function exportGroupPermissions($iblockId)
    {
        $groupHelper = new UserGroupHelper();

        $permissions = $this->getGroupPermissions($iblockId);

        $result = [];
        foreach ($permissions as $groupId => $letter) {
            $groupCode = $groupHelper->getGroupCode($groupId);
            $groupCode = !empty($groupCode) ? $groupCode : $groupId;
            $result[$groupCode] = $letter;
        }

        return $result;
    }

    /**
     * @param $iblockId
     * @param array $permissions
     * @throws HelperException
     */
    public function saveGroupPermissions($iblockId, $permissions = [])
    {
        $groupHelper = new UserGroupHelper();

        $result = [];
        foreach ($permissions as $groupCode => $letter) {
            $groupId = is_numeric($groupCode) ? $groupCode : $groupHelper->getGroupId($groupCode);
            $result[$groupId] = $letter;
        }

        $this->setGroupPermissions($iblockId, $result);
    }

    /**
     * Устанавливает права доступа к инфоблоку для групп
     * предыдущие права сбрасываются
     * принимает массив вида [$groupCode => $letter]
     *
     * @param $iblockId
     * @param array $permissions
     */
    public function setGroupPermissions($iblockId, $permissions = [])
    {
        CIBlock::SetPermission($iblockId, $permissions);
    }

    /**
     * @param $iblockId
     * @param $fields
     * @deprecated
     */
    public function mergeIblockFields($iblockId, $fields)
    {
        $this->saveIblockFields($iblockId, $fields);
    }

    /**
     * @param $code
     * @param string $typeId
     * @throws HelperException
     * @return mixed
     * @deprecated
     */
    public function findIblockId($code, $typeId = '')
    {
        return $this->getIblockIdIfExists($code, $typeId);
    }

    /**
     * @param $code
     * @param string $typeId
     * @throws HelperException
     * @return mixed
     * @deprecated
     */
    public function findIblock($code, $typeId = '')
    {
        return $this->getIblockIfExists($code, $typeId);
    }

    /**
     * @param $iblock int|array
     * @param string $default
     * @return string
     */
    public function getIblockUid($iblock, $default = '')
    {
        if (!is_array($iblock)) {
            //на вход уже пришел uid
            if (false !== strpos($iblock, ':')) {
                return $iblock;
            }

            //на вход пришел id или код инфоблока
            $iblock = $this->getIblock($iblock);
        }

        if (!empty($iblock['IBLOCK_TYPE_ID']) && !empty($iblock['CODE'])) {
            return $iblock['IBLOCK_TYPE_ID'] . ':' . $iblock['CODE'];
        }

        return $default;
    }

    /**
     * @param $iblockUid
     * @return int
     */
    public function getIblockIdByUid($iblockUid)
    {
        $iblockId = 0;

        if (empty($iblockUid)) {
            return $iblockId;
        }

        list($type, $code) = explode(':', $iblockUid);
        if (!empty($type) && !empty($code)) {
            $iblockId = $this->getIblockId($code, $type);
        }

        return $iblockId;
    }

    /**
     * @param $item
     * @return mixed
     */
    protected function prepareIblock($item)
    {
        if (empty($item['ID'])) {
            return $item;
        }
        $item['LID'] = $this->getIblockSites($item['ID']);

        $messages = CIBlock::GetMessages($item['ID']);
        $item = array_merge($item, $messages);
        return $item;
    }

    protected function prepareExportIblockFields($fields)
    {
        if (empty($fields)) {
            return $fields;
        }

        $exportFields = [];
        foreach ($fields as $code => $field) {
            if ($field['VISIBLE'] == 'N' || preg_match('/^(LOG_)/', $code)) {
                continue;
            }
            $exportFields[$code] = $field;
        }

        return $exportFields;
    }

    protected function prepareExportIblock($iblock)
    {
        if (empty($iblock)) {
            return $iblock;
        }

        unset($iblock['ID']);
        unset($iblock['TIMESTAMP_X']);
        unset($iblock['TMP_ID']);

        return $iblock;
    }
}
