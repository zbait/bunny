<?php

namespace Bunny\Database;

/**
 * 分页辅助类
 */
class PageUtil{


    /**
     * 获取分页信息。
     *
     * @param int $recordNum 单页记录数
     * @param int $pageNum 显示多少页
     * @param int $currentPageNum 当前页数
     * @param int $recordTotalNum 总记录数
     *
     * @return array 结果数据集
     */
    public static function getPageInfo(int $recordNum, int $pageNum, int $currentPageNum, int $recordTotalNum){
        $pageTotalNum = ceil($recordTotalNum/$recordNum);
        return array(
            'pageTotalNum' => $pageTotalNum,
            'currentPageNum' => $currentPageNum,
            'pageList' => self::getNumsWithSize($currentPageNum, $pageTotalNum, $pageNum)
        );
    }

    /**
     * 获取指定长度指定中数的连续数据集和。
     *
     * @param int $num 中数
     * @param int $total 最大单数
     * @param int $size 长度
     *
     * @return array 结果数据集
     */
    private static function getNumsWithSize(int $num, int $total, int $size) :array {
        //取最大列表的时候
        if($size >= $total){
            $nums = array();
            for($i = 1;$i<=$total;$i++){
                $nums[] = $i;
            }
            return $nums;
        }
        //二分法便利取值
        $nums = array($num);
        $loop = floor($size/2);
        for(;$loop >= 1;$loop--){
            $nums = self::setPreNum($nums, $total);
            $nums = self::setNextNum($nums, $total);
        }
        return $nums;
    }

    /**
     * 获取上一个数。满足条件则添加，否则补下一个数。
     *
     * @param array $nums 结果数据集
     * @param int $total 最大单数
     *
     * @return array 结果数据集
     */
    private static function setPreNum(array $nums, int $total) :array {
        $preNum = $nums[0] - 1;
        if($preNum <= 0){
            $nums = self::setNextNum($nums, $total);
        }else{
            array_unshift($nums, $preNum);
        }
        return $nums;
    }

    /**
     * 获取下一个数。满足条件则添加，否则补上一个数。
     *
     * @param array $nums 结果数据集
     * @param int $total 最大单数
     *
     * @return array 结果数据集
     */
    private static function setNextNum(array $nums, int $total) :array {
        $nextNum = $nums[count($nums)-1] + 1;
        if($nextNum <= $total){
            $nums[] = $nextNum;
        }else{
            $nums = self::setPreNum($nums, $total);
        }
        return $nums;
    }
}