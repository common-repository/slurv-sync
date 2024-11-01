// Utility method to get values from text inputs
var getInputValue = function($context, name) {
  return $context.find('input[name="' + name + '"]').val();
};

// Renders error message when auth is not successful
var showErrorMessage = function($container) {
  $container.find('.errors').html('<p>Couldn\'t find anyone with that email/password');
};

// Prepares the HTML for rendering the iframe
var renderIframe = function($container, attrs, subdomain, token) {
  // The subdomain is accessible from a hidden field in the form
  var iframeHtml = '<iframe src="http://' + subdomain + '/login?token=';
  // We get the token from our authentication call
  iframeHtml += token;

  // If a sidebar color is present, add the query param
  if (attrs.sidebar) {
    iframeHtml += '&sidebarBg=' + attrs.sidebar;
  }

  // If a custom link color is present, add the query param
  if (attrs.link_color) {
    iframeHtml += '&linkColor=' + attrs.link_color;
  }

  // If a custom logo url is present, add the query param
  if (attrs.logo_url) {
    iframeHtml += '&logoUrl=' + attrs.logo_url;
  }

  // Add the attributes added to the iframe for height and width
  iframeHtml += '" height="' + attrs.height + '" width="' + attrs.width + '"></iframe>';

  // Replace the form with the iframe
  $container.html(iframeHtml);

  // Add this data-attribute to elements that should only be shown to
  // unauthenticated users
  jQuery('[data-slurv-auth-hidden="true"]').hide();

  return true;
};

// jQuery form submit handler

(function($) {
  var $container = $('#slurv-auth-container');
  var $form = $('.slurv-auth-form');

  $form.submit(function(e) {
    e.preventDefault();
    var endpoint = getInputValue($(this), 'endpoint');

    // These are fields that we send to the server
    var postData = {
      email: getInputValue($(this), 'email'),
      password: getInputValue($(this), 'password'),
      subdomain: getInputValue($(this), 'subdomain')
    };

    // These are hidden inputs that we use to render the iframe on successful auth
    var iframeAttrs = {
      sidebar: getInputValue($(this), 'sidebar'),
      link_color: getInputValue($(this), 'link_color'),
      logo_url: getInputValue($(this), 'logo_url'),
      height: getInputValue($(this), 'height'),
      width: getInputValue($(this), 'width')
    };

    // Send credentials to the server
    $.ajax(endpoint, {
      data: postData,
      method: 'POST',
      // Calls renderIframe method with the token we get from the response
      success: function(data) {
        var token = data.users.login_token;
        var subdomain = postData.subdomain;
        renderIframe($container, iframeAttrs, subdomain, token);
      },
      // Shows an error message if email/password is invalid
      error: function(error) {
        showErrorMessage($container);
      }
    });
  });
})(jQuery);
