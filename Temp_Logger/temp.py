import sqlite3
import subprocess
from time import sleep
from datetime import datetime

def ADCtoK(ADC):
	K = .172*ADC - 35.26
	return K

def KtoF(K):
	F = ((K*9)/5)-459.67
	return F

def getAvg():
        total = 0
        for i in range(0,40):
                p = subprocess.Popen(["/usr/sbin/i2cget", "-y", "1", "0x41", "0x06", "w"], stdout=subprocess.PIPE)
                output, err = p.communicate()
                total = total + int(output,16)
                sleep(0.1)
        avgVal = total/40
        return avgVal



conn = sqlite3.connect('tempdata.db')
c = conn.cursor()
c.execute('CREATE TABLE IF NOT EXISTS data(date text, time text, temp real)')
now = datetime.now() #get all the current date and time data.
currentYear = now.year
currentMonth = now.month
currentDay = now.day
currentHour = '%02d' % now.hour
currentMin = '%02d' % now.minute # pad a 0 for time such as 02:04
Ktemp = ADCtoK(getAvg())
Ftemp = KtoF(Ktemp)
Date = str(currentYear)+"-"+str(currentMonth)+"-"+str(currentDay)
Time = str(currentHour)+":"+str(currentMin)
frame = (Date,Time,Ftemp)
c.execute('INSERT INTO data VALUES(?,?,?)',frame)

conn.commit()

conn.close()
