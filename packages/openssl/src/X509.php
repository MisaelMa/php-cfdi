<?php

namespace Cli\Openssl;

use Cli\Openssl\Utils;
use Cli\Openssl\Share;

/**
 *
 */
class X509 extends Share
{
  public array $commandline = [];

  public string $command = 'x509';

  public string $opensslBin = '';

  /**
   *constructor
   */
  public function __construct()
  {
    parent::__construct();
    $this->opensslBin = Utils::getOsComandBin();
    $this->commandline[] = $this->opensslBin;
    $this->commandline[] = $this->command;
  }

  /**
   *help
   */
  public function help(): X509
  {
    return $this;
  }

  /**
   *digest
   */
  public function digest(): X509
  {
    $this->commandline[] = '-digest';
    return $this;
  }

  /**
   *rand
   *
   * @param string $file
   * file
   */
  public function rand(string $file): X509
  {
    $this->commandline[] = "-rand $file";
    return $this;
  }

  /**
   *writerand
   *
   * @param string $file
   * file
   */
  public function writerand(string $file): X509
  {
    $this->commandline[] = "-writerand $file";
    return $this;
  }

  /**
   *engine
   *
   * @param string|int $id
   * id
   */
  public function engine($id): X509
  {
    $this->commandline[] = "-engine $id";
    return $this;
  }

  /**
   *preserve_dates
   */
  public function preserve_dates(): X509
  {
    // todo validate if use options days
    $this->commandline[] = '-preserve_dates';
    return $this;
  }

  /**
   *text
   */
  public function text(): X509
  {
    $this->commandline[] = '-text';
    return $this;
  }

  /**
   *ext
   *
   * @param string $extensions
   * extensions
   */
  public function ext(string $extensions): X509
  {
    $this->commandline[] = "-ext $extensions";
    return $this;
  }

  /**
   *certopt
   *
   * @param string $option
   * option
   */
  public function certopt(string $option): X509
  {
    $this->commandline[] = "-certopt $option";
    return $this;
  }

  /**
   *noout
   */
  public function noout(): X509
  {
    $this->commandline[] = '-noout';
    return $this;
  }

  /**
   *pubkey
   */
  public function pubkey(): X509
  {
    $this->commandline[] = '-pubkey';
    return $this;
  }

  /**
   *modulus
   */
  public function modulus(): X509
  {
    $this->commandline[] = '-modulus';
    return $this;
  }

  /**
   *serial
   */
  public function serial(): X509
  {
    $this->commandline[] = '-serial';
    return $this;
  }

  /**
   *subject_hash
   */
  public function subject_hash(): X509
  {
    $this->commandline[] = '-subject_hash';
    return $this;
  }

  /**
   *issuer_hash
   */
  public function issuer_hash(): X509
  {
    $this->commandline[] = '-issuer_hash';
    return $this;
  }

  /**
   *ocspid
   */
  public function ocspid(): X509
  {
    $this->commandline[] = '-ocspid';
    return $this;
  }

  /**
   *hash
   */
  public function hash(): X509
  {
    $this->commandline[] = '-hash';
    return $this;
  }

  /**
   *subject_hash_old
   */
  public function subject_hash_old(): X509
  {
    $this->commandline[] = '-subject_hash_old';
    return $this;
  }

  /**
   *issuer_hash_old
   */
  public function issuer_hash_old(): X509
  {
    $this->commandline[] = '-issuer_hash_old';
    return $this;
  }

  /**
   *subject
   */
  public function subject(): X509
  {
    $this->commandline[] = '-subject';
    return $this;
  }

  /**
   *issuer
   */
  public function issuer(): X509
  {
    $this->commandline[] = '-issuer';
    return $this;
  }

  /**
   *nameopt
   *
   * @param string $option
   */
  public function nameopt(string $option): X509
  {
    $this->commandline[] = "-nameopt $option";
    return $this;
  }

  /**
   *email
   */
  public function email(): X509
  {
    $this->commandline[] = '-email';
    return $this;
  }

  /**
   *ocsp_uri
   */
  public function ocsp_uri(): X509
  {
    $this->commandline[] = '-ocsp_uri';
    return $this;
  }

  /**
   *startdate
   */
  public function startdate(): X509
  {
    $this->commandline[] = '-startdate';
    return $this;
  }

  /**
   *enddate
   */
  public function enddate(): X509
  {
    $this->commandline[] = '-enddate';
    return $this;
  }

  /**
   *dates
   */
  public function dates(): X509
  {
    $this->commandline[] = '-dates';
    return $this;
  }

  /**
   *checkend
   *
   * @param string|int $num
   * num
   */
  public function checkend($num): X509
  {
    $this->commandline[] = "-checkend $num";
    return $this;
  }

  /**
   *fingerprint
   */
  public function fingerprint(): X509
  {
    $this->commandline[] = '-fingerprint';
    return $this;
  }

  /**
   * C
   */
  public function C(): X509
  {
    $this->commandline[] = '-C';
    return $this;
  }

  /**
   *trustout
   */
  public function trustout(): X509
  {
    $this->commandline[] = '-trustout';
    return $this;
  }

  /**
   *setalias
   *
   * @param string $arg
   * arg
   */
  public function setalias(string $arg): X509
  {
    $this->commandline[] = "-setalias $arg";
    return $this;
  }

  /**
   *alias
   */
  public function alias(): X509
  {
    $this->commandline[] = '-alias';
    return $this;
  }

  /**
   *clrtrust
   */
  public function clrtrust(): X509
  {
    $this->commandline[] = '-clrtrust';
    return $this;
  }

  /**
   *clrreject
   */
  public function clrreject(): X509
  {
    $this->commandline[] = '-clrreject';
    return $this;
  }

  /**
   *addtrust
   *
   * @param string $arg
   * arg
   */
  public function addtrust(string $arg): X509
  {
    $this->commandline[] = "-addtrust $arg";
    return $this;
  }

  /**
   *addreject
   *
   * @param string $arg
   * arg
   */
  public function addreject(string $arg): X509
  {
    $this->commandline[] = "-addreject $arg";
    return $this;
  }

  /**
   *purpose
   */
  public function purpose(): X509
  {
    $this->commandline[] = '-purpose';
    return $this;
  }

  /**
   *sigopt
   *
   * @param string $arg
   * arg
   */
  public function sigopt(): X509
  {
    // todo
    return $this;
  }

  /**
   *clrext
   */
  public function clrext(): X509
  {
    $this->commandline[] = '-clrext';
    return $this;
  }

  /**
   *keyform
   *
   * @param string $options
   * options
   */
  public function keyform(string $options): X509
  {
    if (!in_array($options, ['DER', 'PEM', 'ENGINE'])) {
      throw new \InvalidArgumentException('Invalid keyform option');
    }
    $this->commandline[] = "-keyform $options";
    return $this;
  }

  /**
   *days
   *
   * @param string $arg
   * arg
   */
  public function days(string $arg): X509
  {
    $this->commandline[] = "-days $arg";
    return $this;
  }

  /**
   *x509toreq
   */
  public function x509toreq(): X509
  {
    $this->commandline[] = '-x509toreq';
    return $this;
  }

  /**
   *req
   */
  public function req(): X509
  {
    $this->commandline[] = '-req';
    return $this;
  }

  /**
   *set_serial
   *
   * @param string $n
   * n
   */
  public function set_serial(string $n): X509
  {
    $this->commandline[] = "-set_serial $n";
    return $this;
  }

  /**
   *CA
   *
   * @param string $filename
   * filename
   */
  public function CA(string $filename): X509
  {
    $this->commandline[] = "-CA $filename";
    return $this;
  }

  /**
   *CAkey
   *
   * @param string $filename
   * filename
   */
  public function CAkey(string $filename): X509
  {
    $this->commandline[] = "-CAkey $filename";
    return $this;
  }

  /**
   *
   * CAserial
   *
   * @param string $filename
   * filename
   */
  public function CAserial(string $filename): X509
  {
    $this->commandline[] = "-CAserial $filename";
    return $this;
  }

  /**
   *CAcreateserial
   */
  public function CAcreateserial(): X509
  {
    $this->commandline[] = '-CAcreateserial';
    return $this;
  }

  /**
   *extfile
   *
   * @param string $filename
   * filename
   */
  public function extfile(string $filename): X509
  {
    $this->commandline[] = "-extfile $filename";
    return $this;
  }

  /**
   *extensions
   *
   * @param string $section
   * section
   */
  public function extensions(string $section): X509
  {
    $this->commandline[] = "-extensions $section";
    return $this;
  }

  /**
   *force_pubkey
   *
   * @param string $key
   * key
   */
  public function force_pubkey(string $key): X509
  {
    $this->commandline[] = "-force_pubkey $key";
    return $this;
  }
}

$x509 = new X509();
