<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
class ArticleFactory extends Factory {
 public function definition(): array {
 $title = $this->faker->sentence(6);
 return [
 'title' => $title,
 'slug' => Str::slug($title),
 'content' => $this->faker->paragraphs(5, true),
 'image' => 'https://ui-avatars.com/api/?name=' . urlencode($title) . '&background=f97316&color=fff&size=800',
 'is_published' => true,
 ];
 }
}
