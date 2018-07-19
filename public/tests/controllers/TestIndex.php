<?php
use PHPUnit\Framework\TestCase as TC;

class TestIndex extends TC {
	
	protected $token;	
	
	/**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->token = 'd6b1db3e89a696e03e8acac163f4a0ba';
    }
	
	/**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
		$this->token = NULL;
    }
	
	/**
     * @dataProvider myIndexProvider
     */
    public function testTest($a, $b, $c, $d) {				
		$result=pick('http://putuan.zy52.cn/index/test', ['a'=>$a, 'b'=>$b, 'c'=>$c]);
        $this->assertArrayHasKey('ret', $result);
		$this->assertEquals(0, 		$result['ret']);		
		$this->assertEquals($d,	$result['data']);
    }	
	public function myIndexProvider()
    {
        return [
			['a'=>1, 'b'=>2, 'c'=>3, 'd'=>6],
			['a'=>2, 'b'=>3, 'c'=>4, 'd'=>9],
			['a'=>3, 'b'=>4, 'c'=>5, 'd'=>12],
			['a'=>4, 'b'=>5, 'c'=>6, 'd'=>15],
			['a'=>5, 'b'=>6, 'c'=>7, 'd'=>18],
			['a'=>6, 'b'=>7, 'c'=>8, 'd'=>21],
			['a'=>7, 'b'=>8, 'c'=>9, 'd'=>24],
			['a'=>1000000000000000008, 'b'=>1000000000000000009, 'c'=>10,'d'=>2000000000000000027],
		];
	}
	
	public function testFilter(){
		$result=pick('http://putuan.zy52.cn/index/filter');
		$this->assertNotEmpty($result);
		$this->assertArrayHasKey('ret',  $result);
		$this->assertArrayHasKey('msg',  $result);
		$this->assertArrayHasKey('data', $result);
        $this->assertEquals(0, 	$result['ret']);
		$this->assertEquals('商品列表', 	$result['msg']);
	}
	
	public function testGoods(){		
        $result=pick('http://putuan.zy52.cn/index/goods');
		$this->assertNotEmpty($result['data']);
		return $result['data']['rows'];
	}
	
	/**
     * @depends testGoods
     */
	public function testGoodsDetail($post){
		$this->assertCount(10, $post);
		$result=pick('http://putuan.zy52.cn/index/goodsDetail', ['id'=>$post[1]['id'],'token'=>$this->token]);		
		$this->assertNotEmpty($result['data']);
		$this->assertArrayHasKey('is_favorited', $result['data']);
		$this->assertEquals(209, $result['data']['id']);		
		$this->assertEquals(1, $result['data']['is_favorited']);		
	}
	
	
	
}