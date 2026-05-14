</main>

<footer class="footer">
  <div class="container">
    <div class="footer-inner">
      <a href="index.php" class="footer-logo">DISARM</a>
      <span class="footer-meta">
        Framework v<?= SITE_VERSION ?> &nbsp;·&nbsp;
        <a href="https://www.disarm.foundation/" target="_blank" rel="noopener">disarm.foundation</a>
      </span>
      <div class="footer-links">
        <a href="https://creativecommons.org/licenses/by/4.0/" target="_blank" rel="noopener">CC-BY 4.0</a>
        <a href="search.php">Search</a>
      </div>
    </div>
  </div>
</footer>

<script>
(function(){
  var obs = new IntersectionObserver(function(entries){
    entries.forEach(function(e){
      if(e.isIntersecting){ e.target.classList.add('in'); obs.unobserve(e.target); }
    });
  }, {threshold:0.08});
  document.querySelectorAll('.reveal').forEach(function(el){ obs.observe(el); });
})();
</script>
</body>
</html>
