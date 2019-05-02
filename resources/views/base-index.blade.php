<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>{{ $fillSet->getName() }} by LucidInternets</title>
    <meta name="description" content="Sizable, dynamic placeholder images featuring Bill Murray, with additional variations for grayscale">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
      html, body {
        width: 100vw;
        height: 100vh;
        margin: 0;
        display: flex;
        min-height: 100vh;
        flex-direction: column;
        font-family: Source Sans Pro,system-ui,-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Oxygen,Ubuntu,Cantarell,Fira Sans,Droid Sans,Helvetica Neue;
      }
      .container {
      	width: 960px;
      	margin: 0 auto;
      	overflow: auto;
        display: flex;
      }
      @media (max-width: 960px) {
        .container {
        	width: 100vw;
          padding: 0 1rem;
        }
      }
      main.container {
        flex: 1 0 auto;
        flex-direction: column;
      }
      .column {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
      }
      .row {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
      }
      .container > .row:first-of-type {
        align-items: center;
        margin-top: 1rem;
      }
      .container > .row {
        margin-bottom: 1rem;
      }
      .blurb {
        width: 450px;
      }
    </style>

  </head>
  <body>
    <main class="container">
      <div class="row">
        <div class="blurb">
          <h1>{{ $fillSet->getName() }}</h1>
          @section('content')
              <p>This is my body content.</p>
          @show
          <div class="well">
            <p>Calm: <a href="/200/300" target="_blank">{{ url('/200/300') }}</a></p>
            <p>Gray: <a href="/g/200/300" target="_blank">{{ url('/g/200/300') }}</a></p>
          </div>
        </div>
        <img src="/300/200" width=300 height=200 />
        <img src="/140/200" width=140 height=200 />
      </div>
      <div class="row">
        <img src="/460/300" width=460 height=300 />
        <img src="/g/155/300" width=155 height=300 />
        <div class="column">
          <div class="row">
            <img src="/140/100" width=140 height=100 />
            <img src="/g/140/100" width=140 height=100 />
          </div>
          <div class="row">
            <img src="/284/196" />
          </div>
        </div>
      </div>
    </main>
    <footer class="container">
      <div class="row">
        <span class="by-line">By <a href="https://github.com/mallardduck" target="_blank">mallardduck</a>. | Proudly inspired by: <a href="https://twitter.com/davecowart" target="_blank">@davecowart</a> | </span>
        <span>Check out the sister sites: [Add links later]</span>
      </div>
    </footer>
  </body>
</html>
