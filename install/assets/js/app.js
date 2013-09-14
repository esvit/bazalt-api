"use strict";

var steps = [
    'Step 1',
    'Step 2',
    'Step 3',
    'Step 4'
];

var app = angular.module('bazaltInstaller', []).
    value('InstallScriptUrl', '/install.php').
    config(function($routeProvider, $locationProvider) {
        var newSteps = [];
        for (var i = 0, max = steps.length; i < max; i++) {
            var url = '/step' + (i + 1),
                step = {
                    index: i + 1,
                    title: steps[i],
                    url:   url,
                    invalid: true,
                    callback: null,
                    buttonText: null
                };

            newSteps.push(step);
            $routeProvider.when(url, {
                controller: 'Step' + step.index + 'Ctrl',
                templateUrl: 'install/views/step' + step.index + '.html',
                currentStep: step
            });
            if (i > 0) {
                newSteps[i-1]['next'] = step;
                newSteps[i]['prev'] = newSteps[i-1];
            }
        }
        steps = newSteps;
        $routeProvider.otherwise({ redirectTo:'/step1' });

        $locationProvider.hashPrefix('!');
    });

    app.controller('InstallerCtrl', function ($scope, $location) {
        $scope.steps = steps;
        $scope.nextStep = function(step) {
            if (step.callback) {
                step.callback();
            } else {
                $location.url(step.next.url);
            }
        }

        $scope.prevStep = function(step) {
            $location.url(step.prev.url);
        }

        $scope.goTo = function(step) {
            if (step.index <= $scope.currentStep.index || !step.invalid) {
                $location.url(step.url);
            }
        }

        $location.url('/step1');
        $scope.$on("$routeChangeSuccess", function (event, current, previous) {
            $scope.currentStep = current.$$route.currentStep;
        });
    });

    app.controller('Step1Ctrl', function ($scope, $location, $http, InstallScriptUrl) {
        $scope.currentStep.invalid = false;
        $scope.currentStep.callback = function() {
            if ($scope.isValid()) {
                $location.url($scope.currentStep.next.url);
            } else {
                $scope.checkRequirements();
            }
        };
        $scope.requirements = {
            env: {
                title: 'Enviroment',
                tests: {
                    php54: 'PHP 5.4',
                    mod_rewrite: 'Apache mod_rewrite'
                }
            },
            php: {
                title: 'PHP Extensions',
                tests: {
                    mbstring: 'MB_String module',
                    pdo: 'PDO module',
                    pdo_mysql: 'PDO Mysql module',
                    gd: 'GD module',
                    reflection: 'Reflection class',
                    session: 'Sessions module',
                    json: 'JSON module',
                    filter: 'Filters module',
                    curl: 'CURL library'
                }
            },
            folders: {
                title: 'Folders',
                tests: {
                    config_writable: 'Config file "config.php" writable',
                    tmp_writable: 'Writable tmp/ folder',
                    uploads_writable: 'Writable uploads/ folder',
                    static_writable: 'Writable static/ folder'
                }
            }
        };

        $scope.isValid = function() {
            var valid = true;
            angular.forEach($scope.result, function(item) {
                angular.forEach(item, function(test) {
                    if (test === false) {
                        valid = false;
                    }
                });
            });
            return valid;
        }
        $scope.checkRequirements = function() {
            $scope.loading = true;
            $http.get(InstallScriptUrl)
                 .success(function(result) {
                    $scope.loading = false;
                    $scope.result = result;
                    $scope.currentStep.buttonText = $scope.isValid() ? 'Next' : 'Check again';
                });
        }
        $scope.checkRequirements();
    });

    app.controller('Step2Ctrl', function ($scope, $location, $http, $rootScope, InstallScriptUrl) {
        $scope.currentStep.invalid = false;
        $scope.currentStep.callback = function() {
            $scope.checkConnection($scope.connection);
            return false;
        };
        $scope.currentStep.buttonText = 'Check connection';
        $scope.connection = {
            host: 'localhost',
            user: 'root',
            port: 3306,
            database: 'bazalt_cms',
            create: false
        };
        $scope.checkConnection = function(connection) {
            $scope.error = null;
            $scope.currentStep.loading = true;
            $scope.currentStep.buttonText = 'Loading...';
            $http.post(InstallScriptUrl, connection)
                 .success(function(result) {
                    $scope.currentStep.loading = false;
                    $scope.currentStep.buttonText = 'Next';
                    $rootScope.connection = $scope.connection;
                    $rootScope.languages = result;
                    $location.url($scope.currentStep.next.url);
                 })
                 .error(function(result) {
                    $scope.currentStep.loading = false;
                    $scope.currentStep.buttonText = 'Check again';
                    $scope.error = result;
                 });
        }
    });

    app.controller('Step3Ctrl', function ($scope, $location, $http, InstallScriptUrl) {
        $scope.currentStep.invalid = false;
        $scope.currentStep.callback = function() {
            $scope.createSite($scope.site);
            return false;
        };
        $scope.site = {
            connection: $scope.connection,
            title: 'My first site on BazaltCMS',
            language: $scope.languages[0].id,
            user: 'admin',
            password: 'Pa$Sw0rd'
        };
        $scope.createSite = function(site) {
            $scope.error = null;
            $http.put(InstallScriptUrl, site)
                .success(function(result) {
                    $location.url($scope.currentStep.next.url);
                })
                .error(function(result) {
                    $scope.error = result;
                });
        }
    });

    app.controller('Step4Ctrl', function ($scope) {
        $scope.currentStep.invalid = false;
        $scope.currentStep.callback = function() {
            location.href = '/';
        };
        $scope.currentStep.buttonText = 'Sorry, this is last station';
    });