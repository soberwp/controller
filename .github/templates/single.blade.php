<article @php(post_class())>

  @if($images)
    <ul>
      @foreach($images as $image)
        <li><img src="{{$image['sizes']['thumbnail']}}" alt="{{$image['alt']}}"></li>
      @endforeach
    </ul>
  @endif
  
</article>
