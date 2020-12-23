<?php
/**
 * Ip访问次数统计
 * User: XueSi
 * Email: <1592328848@qq.com>
 * Date: 2020/12/24
 * Time: 0:29
 */

namespace IpLimiterPlugs\IpLimiter;

use EasySwoole\Component\Singleton;
use EasySwoole\Component\TableManager;
use Swoole\Table;

class IpLists
{
    use Singleton;

    /** @var Table */
    protected $table;

    public function __construct()
    {
        TableManager::getInstance()->add('ipList', [
            'ip' => [
                'type' => Table::TYPE_STRING,
                'size' => 16
            ],
            'count' => [
                'type' => Table::TYPE_INT,
                'size' => 8
            ],
            'lastAccessTime' => [
                'type' => Table::TYPE_INT,
                'size' => 8
            ]
        ], 1024 * 128);
        $this->table = TableManager::getInstance()->get('ipList');
    }

    function access(string $ip): int
    {
        $key = substr(md5($ip), 8, 16);
        $info = $this->table->get($key);

        if ($info) {
            $this->table->set($key, [
                'lastAccessTime' => time(),
                'count' => $info['count'] + 1,
            ]);
            return $info['count'] + 1;
        } else {
            $this->table->set($key, [
                'ip' => $ip,
                'lastAccessTime' => time(),
                'count' => $info['count'] + 1,
            ]);
            return 1;
        }
    }

    function clear()
    {
        foreach ($this->table as $key => $item) {
            $this->table->del($key);
        }
    }

    function accessList($count = 10): array
    {
        $ret = [];
        foreach ($this->table as $key => $item) {
            if ($item['count'] >= $count) {
                $ret[] = $item;
            }
        }
        return $ret;
    }

}