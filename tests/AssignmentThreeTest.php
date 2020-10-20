<?php

namespace AssignmentThreeTests;

use AssignmentThree\Database\Connection;
use AssignmentThree\Models\Category;
use AssignmentThree\Models\Comment;
use AssignmentThree\Models\Post;
use AssignmentThree\Models\User;
use Faker\{Factory, Generator};
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

abstract class AssignmentThreeTest extends TestCase
{
	protected static Generator $faker;
	protected static Client $client;
	protected static array $userData;
	protected static User $user;
	protected static array $categoryData;
	protected static Category $category;

	public static function setUpBeforeClass(): void
	{
		self::$client = new Client(['base_uri' => 'http://apache/Assignments/3/Solution/public/']);
		self::$faker = Factory::create();
	}

	protected static function generateUserData(string $username = null, string $email = null, string $password = null): array
	{
		return [
			'username' => $username ?? self::$faker->username,
			'email' => $email ?? self::$faker->email,
			'password' => $password ?? self::$faker->password
		];
	}

	protected static function generateUser(string $username = null, string $email = null, string $password = null): User
	{
		$userData = self::generateUserData($username, $email, $password);

		return User::create(
			$userData['username'],
			$userData['email'],
			$userData['password']
		);
	}

	protected static function generateCategory(User $user = null, string $title = null): Category
	{
		$user = $user ?? self::generateUser();
		$categoryData = self::generateCategoryData($user, $title);

		return Category::create(
			$categoryData['createdBy'],
			$categoryData['title'],
			$categoryData['description']
		);
	}

	protected static function generateCategoryData(User $user = null, string $title = null): array
	{
		$user = $user ?? self::generateUser();
		$title = $title ?? self::$faker->word;

		while (Category::findByTitle($title)) {
			$title = self::$faker->word;
		}

		return [
			'createdBy' => $user->getId(),
			'title' => $title,
			'description' => self::$faker->sentence
		];
	}

	protected static function generatePost(string $type = null, User $user = null, Category $category = null): Post
	{
		$postData = self::generatePostData($type, $user, $category);

		return Post::create(
			$postData['userId'],
			$postData['categoryId'],
			$postData['title'],
			$postData['type'],
			$postData['content']
		);
	}

	protected static function generatePostData(string $type = null, User $user = null, Category $category = null): array
	{
		$postData['userId'] = empty($user) ? self::generateUser()->getId() : $user->getId();
		$postData['categoryId'] = empty($category) ? self::generateCategory()->getId() : $category->getId();
		$postData['title'] = self::$faker->word;

		if (!empty($type)) {
			if ($type === 'Text') {
				$postData['type'] = 'Text';
				$postData['content'] = self::$faker->paragraph();
			} else {
				$postData['type'] = 'URL';
				$postData['content'] = self::$faker->url;
			}
		} else if (rand(0, 1) === 0) {
			$postData['type'] = 'Text';
			$postData['content'] = self::$faker->paragraph();
		} else {
			$postData['type'] = 'URL';
			$postData['content'] = self::$faker->url;
		}

		return $postData;
	}

	protected static function generateComment(int $postId = null, int $userId = null, int $replyId = null): Comment
	{
		$comment = self::generateCommentData($postId, $userId, $replyId);

		return Comment::create(
			$comment['postId'],
			$comment['userId'],
			$comment['content'],
			$comment['replyId']
		);
	}

	protected static function generateCommentData(int $postId = null, int $userId = null, int $replyId = null): array
	{
		$postId = $postId ?? self::generatePost()->getId();
		$userId = $userId ?? self::generateUser()->getId();

		return [
			'postId' => $postId,
			'userId' => $userId,
			'content' => self::$faker->paragraph(),
			'replyId' => $replyId
		];
	}

	public function tearDown(): void
	{
		$tables = ['category', 'comment', 'post', 'user'];
		$database = new Connection();
		$connection = $database->connect();

		foreach ($tables as $table) {
			$statement = $connection->prepare("DELETE FROM `$table`");
			$statement->execute();
			$statement = $connection->prepare("ALTER TABLE `$table` AUTO_INCREMENT = 1");
			$statement->execute();
		}

		$statement->close();
	}

	protected function getJsonResponse(string $method = 'GET', string $url = '', array $data = [])
	{
		$request = $this->buildRequest($method, $url, $data);
		$response = self::$client->request(
			$request['method'],
			$request['url'],
			$request['body']
		)->getBody();
		$jsonResponse = json_decode($response, true);
		return $jsonResponse;
	}

	protected function buildRequest(string $method, string $url, array $data): array
	{
		$body['form_params'] = [];

		foreach ($data as $key => $value) {
			$body['form_params'][$key] = $value;
		}

		return [
			'method' => $method,
			'url' => $url,
			'body' => $body
		];
	}
}
