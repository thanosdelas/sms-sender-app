<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
  </head>
  <body>
    <h4>{{ $title }}</h4>

    <div>
      Api Routes:
      @foreach ($api_routes as $route)
      <div>
        <a href="{{ url('/') }}/{{ $route }}" target="_blank">{{ url('/') }}/{{ $route }}</a>
      </div>
      @endforeach
    </div>
  </body>
</html>
