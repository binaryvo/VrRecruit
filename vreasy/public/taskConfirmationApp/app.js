angular.module('taskConfirmationApp',  ['ui.router', 'ngResource'])
.config(function($stateProvider, $urlRouterProvider, $locationProvider) {
    // Use hashtags in URL
    $locationProvider.html5Mode(false);

    $urlRouterProvider.otherwise("/");
    $stateProvider
        .state('index', {
          url: "/",
          templateUrl: "/taskConfirmationApp/templates/index.html",
          controller: 'TaskCtrl'
        })
        .state('task', {
          url: "/task/:id",
          templateUrl: "/taskConfirmationApp/templates/task.html",
          controller: 'TaskCtrl'
        })
        .state('new', {
          url: "/new",
          templateUrl: "/taskConfirmationApp/templates/create.html",
          controller: 'NewTaskCtrl'
        });
})
.factory('Task', function($resource) {
    return $resource('/task/:id?format=json',
        {id:'@id'},
        {
            'get': {method:'GET'},
            'save': {method: 'PUT'},
            'create': {method: 'POST'},
            'query': {method:'GET', isArray:true},
            'remove': {method:'DELETE'},
            'delete': {method:'DELETE'}
        }
    );
})
.factory('Message', function($resource) {
    return $resource('/messages/:id?format=json',
        {id:'@id', task_id: '@task_id'},
        {
            'get': {method:'GET'},
            'save': {method: 'PUT'},
            'create': {method: 'POST'},
            'query': {method:'GET', isArray: true}
        }
    );
})
.factory('Status', function($resource) {
    return $resource('/task-status-history/:id?format=json',
        {task_id: '@task_id'},
        {
            'query':  {method:'GET', isArray: true}
        }
    );
})
.controller('NewTaskCtrl', [
    '$scope', '$location', 'Task',
    function($scope, $location, Task) {
        $scope.item = {id:0,assinged_name:'',assigned_phone:'',deadline:''};
        $scope.save = function() {
          Task.create($scope.item/*, function(){ $location.path('/'); }*/);
        };
}])
.controller('TaskCtrl', [
    '$scope', '$stateParams', 'Task', 'Message', 'Status',
    function($scope, $stateParams, Task, Message, Status) {
        if (angular.isDefined($stateParams.id)) {
            $scope.task = Task.get({id: $stateParams.id});
            $scope.messages = Message.query({task_id: $stateParams.id});
            $scope.statuses = Status.query({task_id: $stateParams.id});

            $scope.sendMessage = function(e){
                var message = {
                    task_id: $stateParams.id,
                    from: true,
                    direction: 'to',
                    text: $scope.text,  
                    created_at: new Date()
                };
                var response = Message.create(message);
                $scope.messages.push(message);
                $scope.text = "";
            };
        } else {
            $scope.tasks = Task.query();    
        }
}]);