import java.time.LocalDate;
import java.time.temporal.ChronoUnit;
import java.util.Scanner;

class Employee {
    String id;
    String name;
    String dob;
    String email;
    LocalDate joiningDate;
    String type;

    int vacationLimit;
    int sickLimit;

public Employee(){}

    public Employee(String id, String name, String dob, String email, LocalDate joiningDate, String type) {
        this.id = id;
        this.name = name;
        this.dob = dob;
        this.email = email;
        this.joiningDate = joiningDate;
        this.type = type;

        if(type.equalsIgnoreCase("Officer")){
            vacationLimit = 15;
            sickLimit = 10;
        }
        else{
            vacationLimit = 10;
            sickLimit = 7;
        }
    }

    public int calculateLeave(int totalLeave){


        LocalDate endDate = LocalDate.of(2025,12,31);

        long daysWorked = ChronoUnit.DAYS.between(joiningDate, endDate) + 1;

        int daysInYear = joiningDate.isLeapYear() ? 366 : 365;

        double result = (daysWorked * totalLeave) / (double) daysInYear;

        if(result < 0.5)
            return (int)Math.floor(result);
        else
            return (int)Math.ceil(result);
    }

    public void display(){

        int vacation = calculateLeave(vacationLimit);
        int sick = calculateLeave(sickLimit);

        System.out.println("\n....................Employee Details....................");
        System.out.println("ID: " + id);
        System.out.println("Name: " + name);
        System.out.println("DOB: " + dob);
        System.out.println("Email: " + email);
        System.out.println("Joining Date: " + joiningDate);
        System.out.println("Type: " + type);
        System.out.println("Vacation Leave: " + vacation);
        System.out.println("Sick Leave: " + sick);
    }


}

public class Main {

    public static void main(String[] args) {

        Scanner sc = new Scanner(System.in);

        for(int i=1;i<=1;i++){

            System.out.println("\n-------------Enter Employee " + i + " Information-------------");

            System.out.print("ID: ");
            String id = sc.nextLine();

            System.out.print("Name: ");
            String name = sc.nextLine();

            System.out.print("Date of Birth: ");
            String dob = sc.nextLine();

            System.out.print("Email: ");
            String email = sc.nextLine();

            System.out.print("Joining Date (YYYY-MM-DD): ");
            LocalDate joiningDate = LocalDate.parse(sc.nextLine());

            System.out.print("Employee Type (Officer/Staff): ");
            String type = sc.nextLine();

            Employee emp = new Employee(id,name,dob,email,joiningDate,type);

            emp.display();
        }


        sc.close();
    }
}
