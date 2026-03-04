# PROMPT

## TeleGen
Generates a real-time modifiable telemetry monitor for a simulated patient.
Admin can control settings and the program will generate live EKG tracing and other vital signs. This output is viewable to the viewer in a format that resembles a telemetry monitor such as the Phillips IntelliVue MP5. ⚕

Logic for different heart rhythms

**Sinus Rhythm:**
ADMIN SETS:
    sa_node_rate
        Range: 20-220 beats/min, defaults to 80
When no waves are occurring, the tracing is a flat line (or could have slight variation to simulate artifact for realism)
The SA Node Rate determines the time interval between P waves (P wave interval = 60 / SA Node Rate)
The program determines the time passed since the previous P wave occurred. If this equals or exceeds the P wave interval, a new P wave is generated
If a P wave occurs, then a QRS complex is generated after a set delay (this is true for sinus rhythm but not 3rd Degree AV Block)
If a QRS complex occurs, then a T wave is generated after a set delay

It would also be ideal to have the T wave duration (width on x-axis) vary with the heart rate.

Advanced option: introduce mild variability?

**Atrial fibrillation:**
ADMIN SETS:
    ventricular_response_rate
        Range: 20-220 beats/min, defaults to 150
Instead of a flat line, the tracing is random noise at low amplitude
The program determines a Ventricular Response Interval based on the set Ventricular Response Rate modifies it by randomly increasing or decreasing the interval by up to 400 ms
The program determines the time passed since the previous QRS complex occurred. If this equals or exceeds the Ventricular Response Interval a new QRS complex is generated
The program AGAIN determines a new Ventricular Response Interval based on the set Ventricular Response Rate modifies it by randomly increasing or decreasing the interval by up to 400 ms
If a QRS complex occurs, then a T wave is generated after a set delay

**Atrial flutter**
ADMIN SETS:
    flutter_response_ratio
        Options: 1:1, 1:2, 1:3, 1:4, default to 1:2
Instead of a flat line, the tracing is a continuos sawtooth pattern, each upward deflection representing a flutter wave. The rate of this should be 300 flutter waves/min.
In 1:1 mode, each flutter wave triggers a QRS complex to occur -- this should overlap the flutter waves and not stop the continous flutter waves from cycling regularly at 300/min.
In 1:2 mode, every other flutter wave triggers a QRS complex
etc.
If a QRS complex occurs, then a T wave is generated after a set delay. This wave should also be summed with the continuous underling flutter wave and not interrupt the continuous flutter wave cycling

Advanced: variable response rate

**SVT**
ADMIN SETS: svt_rate
QRS complex occur based on the rate
If a QRS complex occurs, then a T wave is generated after a set delay.

**VF**
ADMIN SETS: N/A
Random noise, higher amplitude and coarser than the a fib noise

**Monomorphic V Tach**
ADMIN SETS: vt_rate
Sine wave works as a placeholder, but can improve this by generating wide QRS complexes at rate set by the Admin and adding abnormal T waves which will mostly overlap subsequent QRS complex
Advanced option: fusion and capture beats with ongoing atrial activity

**Torsades de pointes**
ADMIN SETS: torsades_rate
Should be able to simulate this nicely with a fast sine wave for which the amplitude is then further modulated by a second much slower sine wave

**1st degree AV block**
ADMIN SETS: sa_node_rate, pr_prolongation
Identical to sinus rhythm except the PR interval is prolonged

**2nd degree AV block, Type I**
ADMIN SETS: sa_node_rate, block_severity -- this one might be tough to figure out

**2nd degree AV block, Type II**
ADMIN SETS: sa_node_rate, block_severity?

**3rd degree AV block**
ADMIN SETS: sa_node_rate, escape_rate

**Asystole**
ADMIN SET: N/A
