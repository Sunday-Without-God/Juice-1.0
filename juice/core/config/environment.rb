#!/usr/bin/env ruby
#encoding:utf-8

require 'rubygems'
gem 'bundler'
require 'bundler'

require 'yaml'

data = File.read(File.join(File.dirname(__FILE__), "config.yml"))

config = YAML.load(data)

Bundler.setup(:default, config[:mode])

AppPath = File.expand_path File.join(File.dirname(__FILE__), "..")