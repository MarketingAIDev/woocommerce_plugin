		// return UTC time, in milliseconds	
		let getUTCTime = () =>{

			let now = new Date();

				let timeZoneOffset = now.getTimezoneOffset();
				let timeZoneOffsetSec =timeZoneOffset*60;
				let timeZoneOffsetMilli = timeZoneOffsetSec*1000;

			let nowUTC = new Date( now.getTime() + timeZoneOffsetMilli  );

			return nowUTC.getTime();
		}




        		// Calculate and Return message age
		let getMessageAge = dateCreated => {
			
			// Create JavaScript date from text date
			let creationDate = new Date( dateCreated );

			let milliDifference = getUTCTime()  - creationDate.getTime();
			let secondsDifference = milliDifference/1000;
			let minutesDifference = secondsDifference/60;
			let hoursDifference = minutesDifference/60;
			let daysDifference = hoursDifference/24;
			let weeksDifference = daysDifference/7;
			let monthsDifference = daysDifference/30;
			let yearsDifference = daysDifference/365;


				// If message age is less than a minute show in seconds
				if(  Math.round( secondsDifference ) < 60  ){
					return Math.round( secondsDifference  ) + " sec ago" ;
					//eturn "less than a minute";
				} 

				// Else if message age is greater than a minute show in minutes				
				else if( Math.round( minutesDifference<60 )  ){
					return Math.round( minutesDifference  ) + " min ago" ;
				} 

				// Else if message age is greater than 60 minute show in hours				
				else if( Math.round( hoursDifference<24  )){					
					return Math.round( hoursDifference  ) + " hr ago" ;
				}
				
				// Else if message age is greater than 24 hours show in days												
				else if( Math.round( daysDifference<7  )){					
					return Math.round( daysDifference  ) + " dys ago" ;
				}
				
				// Else if message age is greater than 7 days show in weeks																				
				else if( Math.round( daysDifference<30  )){					
					return Math.round( weeksDifference  ) + " wks ago" ;
				}
				
				// Else if message age is greater than 30  days show in months																				
				else if( Math.round( monthsDifference<12  )){					
					return Math.round( monthsDifference  ) + " mnths ago" ;
				}
				
				// Else if  message age is greater than A year  days show in years																								
				else{
					return Math.round( yearsDifference  ) + " yrs ago" ;
				}
		}



        let roundBytesToAppropriateSize = sizeInByte =>{

            let filesize = Number.parseInt( sizeInByte );

            if(filesize>1000000000){
    
                filesize = (filesize/1000000000);
                filesize = Math.round( filesize * 100  ) / 100 ; 
    
                filesize +=' GB';
            }
    
            else if(filesize>1000000){
                filesize = (filesize/1000000);
                filesize = Math.round( filesize * 100  ) / 100 ; 
                filesize +=' MB';
            } 
            else if(filesize>1000){
                filesize = (filesize/1000);
                filesize = Math.round( filesize * 100  ) / 100 ; 
                filesize +=' Kb';
            }
             else{
                filesize = Math.round( filesize * 100  ) / 100 ; 
                filesize += ' byte';
            }

            return filesize
    
        }

  