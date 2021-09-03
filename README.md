# Laravel pipeline patterni

## Pipeline Pattern

Pipeline pattern - bu dizayn pattern bo'lib, u obyektda bo'ladigan qadamli o'zgarishlar bilan ishlashda foydalaniladi. Ya'ni, bitta umumiy jarayonni qadamlarga bo'lib bajariladi, jarayon oxirida esa bir butun natija olinadi.

## Misol

Pipeline patternga misol qilib laravel-da filterlash funksiyasini yozib ko'ramiz. Aytaylik, chiqariladigan postlarni aktiv, aktivmasligiga qarab, yoki ASC, DESC ko'rinishida saralab chiqarishimiz kerak.

Avval, laravelda yuqoridagi vazifaning oddiy ko'rinishda yozilgan kodiga misol ko'raylik. PostController-ning index metodida post-larni filterlash quyidagicha amalga oshirilgan:

```
    class PostController extends Controller
    {
        public function index(Request $request)
        {
            $query = Post::query();

            if ($request->has('active')) {
                $query->where('active', $request->input('active'));
            }

            if ($request->has('sort')) {
                $query->orderBy('title', $request->input('sort'));
            }

            $posts = $query->get();

            return response()->json([$posts]);
        }
    }
```
Endi, shu kodni pipeline patterni yordamida yozamiz. 
1. app/Filters papkasida Active va Sort klasslarini yozamiz:

Active.php

```
    class Active {
        public function handle($request, Closure $next)
        {
            if (!request()->has('active')) {
                return $next($request);
            }

            return $next($request)->where('active', request()->input('active'));
        }
    }
```

Sort.php

```
    class Sort {
        public function handle($request, Closure $next)
        {
            if (!request()->has('sort')) {
                return $next($request);
            }

            return $next($request)->orderBy('title', request()->input('sort'));
        }
    }
```

2. PostController.php:

```
    use Illuminate\Pipeline\Pipeline;

    class PostController extends Controller
    {
        public function index(Request $request)
        {
            $posts = app(Pipeline::class)
                    ->send(Post::query())
                    ->through([
                        \App\Filters\Active::class,
                        \App\Filters\Sort::class
                    ])
                    ->thenReturn()
                    ->get();

            return response()->json([$posts]);
        }
    }
```
PostController.php index metodida:
* `send()` metodi query-ni handle metodiga uzatadi
* `through()` klaslarni parametr sifatida olib uzatadi
* `thenReturn()` yakuniy natijani qaytarib beradi.
