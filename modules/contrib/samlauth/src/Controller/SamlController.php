<?php

namespace Drupal\samlauth\Controller;

use Exception;
use Drupal\samlauth\SamlService;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\Core\Utility\Error;
use OneLogin_Saml2_Utils;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * Returns responses for samlauth module routes.
 */
class SamlController extends ControllerBase {

  /**
   * The samlauth SAML service.
   *
   * @var \Drupal\samlauth\SamlService
   */
  protected $saml;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructor for Drupal\samlauth\Controller\SamlController.
   *
   * @param \Drupal\samlauth\SamlService $saml
   *   The samlauth SAML service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(SamlService $saml, RequestStack $request_stack) {
    $this->saml = $saml;
    $this->requestStack = $request_stack;
  }

  /**
   * Factory method for dependency injection container.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('samlauth.saml'),
      $container->get('request_stack')
    );
  }

  /**
   * Initiates a SAML2 authentication flow.
   *
   * This should redirect to the Login service on the IDP and then to our ACS.
   */
  public function login() {
    try {
      $this->saml->login($this->getUrlFromDestination());
      // We don't return here unless something is fundamentally wrong inside the
      // SAML Toolkit sources.
      throw new Exception('Not redirected to SAML IDP');
    }
    catch (Exception $e) {
      $this->handleException($e, 'initiating SAML login');
    }
    return new RedirectResponse(Url::fromRoute('<front>', [], ['absolute' => TRUE])->toString());
  }

  /**
   * Initiate a SAML2 logout flow.
   *
   * This should redirect to the SLS service on the IDP and then to our SLS.
   */
  public function logout() {
    try {
      $this->saml->logout($this->getUrlFromDestination());
      // We don't return here unless something is fundamentally wrong inside the
      // SAML Toolkit sources.
      throw new Exception('Not redirected to SAML IDP');
    }
    catch (Exception $e) {
      $this->handleException($e, 'initiating SAML logout');
    }
    return new RedirectResponse(Url::fromRoute('<front>', [], ['absolute' => TRUE])->toString());
  }

  /**
   * Displays service provider metadata XML for iDP autoconfiguration.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function metadata() {
    try {
      $metadata = $this->saml->getMetadata();
    }
    catch (Exception $e) {
      $this->handleException($e, 'processing SAML SP metadata');
      return new RedirectResponse(Url::fromRoute('<front>', [], ['absolute' => TRUE])->toString());
    }

    $response = new Response($metadata, 200);
    $response->headers->set('Content-Type', 'text/xml');
    return $response;
  }

  /**
   * Attribute Consumer Service.
   *
   * This is usually the second step in the authentication flow; the Login
   * service on the IDP should redirect (or: execute a POST request to) here.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function acs() {
    try {
      $this->saml->acs();
      $url = $this->getRedirectUrlAfterProcessing(TRUE);
    }
    catch (Exception $e) {
      $this->handleException($e, 'processing SAML authentication response');
      $url = Url::fromRoute('<front>', [], ['absolute' => TRUE])->toString();
    }

    return new RedirectResponse($url);
  }

  /**
   * Single Logout Service.
   *
   * This is usually the second step in the logout flow; the SLS service on the
   * IDP should redirect here.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *
   * @todo we already called user_logout() at the start of the logout
   *   procedure i.e. at logout(). The route that leads here is only accessible
   *   for authenticated user. So in a logout flow where the user starts at
   *   /saml/logout, this will never be executed and the user gets an "Access
   *   denied" message when returning to /saml/sls; this code is never executed.
   *   We should probably change the access rights and do more checking inside
   *   this function whether we should still log out.
   */
  public function sls() {
    try {
      $this->saml->sls();
      $url = $this->getRedirectUrlAfterProcessing();
    }
    catch (Exception $e) {
      $this->handleException($e, 'processing SAML aingle-logout response');
      $url = Url::fromRoute('<front>', [], ['absolute' => TRUE])->toString();
    }

    return new RedirectResponse($url);
  }

  /**
   * Change password redirector.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function changepw() {
    $url = \Drupal::config('samlauth.authentication')->get('idp_change_password_service');
    return new RedirectResponse($url);
  }

  /**
   * Constructs a full URL from the 'destination' parameter.
   *
   * @return string|null
   *   The full absolute URL (i.e. leading back to ourselves), or NULL if no
   *   destination parameter was given. This value is tuned to what login() /
   *   logout() expect for an input argument.
   *
   * @throws \RuntimeException
   *   If the destination is disallowed.
   */
  protected function getUrlFromDestination() {
    $destination_url = NULL;
    $destination = $this->requestStack->getCurrentRequest()->query->get('destination');
    if ($destination) {
      if (UrlHelper::isExternal($destination)) {
        // Prevent authenticating and then redirecting somewhere else.
        throw new \RuntimeException("Destination URL query parameter must not be external: $destination");
      }
      // The destination parameter is relative by convention but fromUserInput()
      // requires it to start with '/'. (Note '#' and '?' don't make sense here
      // because that would be expanded to the current URL, which is saml/*.)
      if (strpos($destination, '/') !== 0) {
        $destination = "/$destination";
      }
      $destination_url = Url::fromUserInput($destination)->setAbsolute()->toString();
    }

    return $destination_url;
  }

  /**
   * Returns a URL to redirect to.
   *
   * This should be called only after successfully processing an ACS/logout
   * response.
   *
   * @param bool $logged_in
   *   (optional) TRUE if an ACS request was just processed.
   *
   * @return string|null
   *   The URL to redirect to.
   */
  protected function getRedirectUrlAfterProcessing($logged_in = FALSE) {
    $url = '';
    if (isset($_REQUEST['RelayState'])) {
      // We should be able to trust the RelayState parameter at this point
      // because the response from the IDP was verified. Only validate general
      // syntax.
      if (!UrlHelper::isValid($_REQUEST['RelayState'], TRUE)) {
        $this->getLogger('samlauth')->error('Invalid RelayState parameter found in request: @relaystate', ['@relaystate' => $_REQUEST['RelayState']]);
      }
      // The SAML toolkit set a default RelayState to itself (saml/log(in|out))
      // when starting the process; ignore this.
      elseif (strpos($_REQUEST['RelayState'], OneLogin_Saml2_Utils::getSelfURLhost() . '/saml/') !== 0) {
        $url = $_REQUEST['RelayState'];
      }
    }

    if (!$url) {
      // If no url was specified, we have a hardcoded route to redirect to.
      $route = $logged_in ? 'user.page' : '<front>';
      $url = Url::fromRoute($route, [], ['absolute' => TRUE])->toString();
    }

    return $url;
  }

  /**
   * Displays error message and logs full exception.
   *
   * @param $exception
   *   The exception thrown.
   * @param string $while
   *   A description of when the error was encountered.
   */
  protected function handleException($exception, $while = '') {
    if ($while) {
      $while = " $while";
    }
    // We use the same format for logging as Drupal's ExceptionLoggingSubscriber
    // except we also specify where the error was encountered. (The options are
    // limited, so we make this part of the message, not a context parameter.)
    $error = Error::decodeException($exception);
    unset($error['severity_level']);
    $this->getLogger('samlauth')->critical("%type encountered while $while: @message in %function (line %line of %file).", $error);
    // Don't expose the error to prevent information leakage; the user probably
    // can't do much with it anyway. But hint that more details are available.
    drupal_set_message("Error $while; details have been logged.", 'error');
  }
}
