# team-winning-chances
A mini Symfony 5 project to check whether a team can win by rearranging its order of players competing with the other team.

## Project setup
  * Fetch the repository.
  * Create & setup .env file similiar to .env.exmaple

## Command to run program
  * Comand can be run in **2** ways:
    * **Interactive user input** -  
        * Run this console: php bin/console app:find-team-wining-chance 
        * **Note: Input should be comma separted integers only**
    * **Full command** -    
       * php bin/console app:find-team-wining-chance --team_a_values={__**Comma separated array values**__} --team_b_values={__**Comma separated array values**__}
       * Ex. - php bin/console app:find-team-wining-chance --team_a_values=35,100,20,50,40 --team_b_values=35,10,30,20,90

## Constraints
  * Only comma-separated values allowed in input other than these values exception would be thrown to the user.
  * Assumption has been taken in the code to allow a player of team A to compete only once with team B player.

