<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="theme-color" content="#ffffff">
    <title>{{ $title ?? 'Error' }} | {{ isset($subdomain) ? $subdomain->getName() :  'Filler Images' }} by LucidInternets</title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Ruh-roh, Gang! Looks like an error occured.">
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Open+Sans">

    @isset($refresh)
      <meta http-equiv="refresh" content="4; url={{ $refresh }}" />
    @endisset


    <style>
      html {
          background-color: #fefefe;
      }
      body {
          font-family: Open Sans,Arial;
          color: #050505;
          font-size: 16px;
          margin: 0;
          line-height: 1.4;
          text-align: justify;
          display: flex;
          min-height: 100vh;
          flex-direction: column;
      }
      body > main {
        flex: 1 1 auto;
        margin: 2em auto;
        width: 100%;
        max-width: 800px;
        padding: 1em;
      }
      body > footer {
        width: 100%;
        max-width: 800px;
        margin: 0 auto;
        padding: 0 1rem;
      }
      @media screen and (max-width:500px) {
          body {
              text-align: left;
          }
      }
    </style>
</head>

<body>
    <main>
      <h1>{{ $title }}</h1>
      <p>{!! $message ?? 'Something went wrong with the request.' !!}</p>
    </main>

    <footer class="container">
      <div class="row">
        <span class="by-line">By <a href="https://github.com/mallardduck" target="_blank">mallardduck</a>.</span>
        @if (isset($sites))
         |
        <span>
          Check out the Filler Image sites:
          @foreach ($sites as $site)
            @php
              list($name, $url) = $site;
            @endphp
            <a href="{{ $url }}">{{ $name }}</a>
          @endforeach
        </span>
        @endif
      </div>
    </footer>
</body>

</html>
